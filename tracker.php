<?php
require __DIR__.'/db_connect.php';

// Hilfsfunktion: Kalorien eines Gerichts berechnen
function mealCalories(PDO $pdo, int $meal_id): int {
  $sql = 'SELECT mi.quantity_grams, f.calories_per_100g
          FROM meal_items mi
          JOIN foods f ON f.id = mi.food_id
          WHERE mi.meal_id = ?';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$meal_id]);
  $sum = 0.0;
  foreach ($stmt as $row) {
    $sum += ($row['quantity_grams'] / 100.0) * (int)$row['calories_per_100g'];
  }
  return (int)round($sum);
}

$meals = $pdo->query('SELECT id, name FROM meals ORDER BY name')->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $meal_id = (int)($_POST['meal_id'] ?? 0);
  $servings = (float)($_POST['servings'] ?? 1);
  $consumed_at = trim($_POST['consumed_at'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  if ($meal_id <= 0) { $errors[] = 'Bitte ein Gericht wählen.'; }
  if ($servings <= 0) { $servings = 1; }
  if ($consumed_at === '') { $errors[] = 'Bitte Datum/Zeit angeben.'; }

  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO consumptions (meal_id, servings, consumed_at, notes) VALUES (?,?,?,?)');
    $stmt->execute([$meal_id, $servings, $consumed_at, $notes ?: null]);
    header('Location: /tracker.php?added=1&date='.urlencode(substr($consumed_at, 0, 10)));
    exit;
  }
}

// Datum für Ansicht (?date=YYYY-MM-DD)
$today = (new DateTime('now'))->format('Y-m-d');
$viewDate = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'] ?? '') ? $_GET['date'] : $today);

// Konsumtionen des Tages laden
$sql = 'SELECT c.id, c.servings, c.consumed_at, c.notes, m.id AS meal_id, m.name
        FROM consumptions c
        JOIN meals m ON m.id = c.meal_id
        WHERE DATE(c.consumed_at) = ?
        ORDER BY c.consumed_at';
$stmt = $pdo->prepare($sql);
$stmt->execute([$viewDate]);
$rows = $stmt->fetchAll();

// Summen berechnen
$total = 0;
$entries = [];
foreach ($rows as $r) {
  $base = mealCalories($pdo, (int)$r['meal_id']);
  $kcal = (int)round($base * (float)$r['servings']);
  $total += $kcal;
  $entries[] = [
    'time' => substr($r['consumed_at'], 11, 5),
    'name' => $r['name'],
    'servings' => $r['servings'],
    'kcal' => $kcal,
    'notes' => $r['notes']
  ];
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Tracker – CalTrack</title>
  <link rel="stylesheet" href="/styles.css"/>
</head>
<body>
<?php include __DIR__.'/header.php'; ?>
<main class="container" style="padding:28px 0;">
  <h1>Tracker</h1>
  <p class="info">Wähle ein gespeichertes Gericht, trage Portionen und Datum/Zeit ein. Die Tagesansicht zeigt die Summe.</p>

  <?php if (!empty($_GET['added'])): ?>
    <div class="card" style="border-left:4px solid var(--success);">
      Eintrag gespeichert ✅
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="card" style="border-left:4px solid var(--danger);">
      <strong>Fehler:</strong>
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <section class="card">
    <form method="post" class="form">
      <div class="row">
        <select class="select" name="meal_id" required>
          <option value="">– Gericht wählen –</option>
          <?php foreach ($meals as $m): ?>
            <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <input class="input" type="number" step="0.25" min="0.25" name="servings" placeholder="Portionen (z. B. 1, 0.5)" value="1"/>
      </div>
      <div class="row">
        <input class="input" type="datetime-local" name="consumed_at" value="<?= htmlspecialchars($today.'T12:00') ?>" required/>
        <input class="input" name="notes" placeholder="Notiz (optional)"/>
      </div>
      <button class="button" type="submit">Eintragen</button>
    </form>
  </section>

  <section class="card">
    <div class="row" style="justify-content: space-between;">
      <form method="get" class="row" action="/tracker.php">
        <input class="input" type="date" name="date" value="<?= htmlspecialchars($viewDate) ?>"/>
        <button class="button secondary">Tag anzeigen</button>
      </form>
      <div class="meta">Summe am <?= htmlspecialchars($viewDate) ?>: <b><?= (int)$total ?> kcal</b></div>
    </div>

    <table class="table" style="margin-top:10px;">
      <thead>
        <tr>
          <th>Zeit</th>
          <th>Gericht</th>
          <th>Portionen</th>
          <th>kcal</th>
          <th>Notiz</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($entries as $e): ?>
        <tr>
          <td class="meta"><?= htmlspecialchars($e['time']) ?></td>
          <td><?= htmlspecialchars($e['name']) ?></td>
          <td><?= htmlspecialchars($e['servings']) ?></td>
          <td><b><?= (int)$e['kcal'] ?></b></td>
          <td class="meta"><?= htmlspecialchars($e['notes'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$entries): ?>
        <tr><td colspan="5" class="meta">Keine Einträge für diesen Tag.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</main>
</body>
</html>
