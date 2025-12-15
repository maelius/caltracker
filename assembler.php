<?php
require __DIR__.'/db_connect.php';

// Alle Foods holen
$foods = $pdo->query('SELECT id, name, calories_per_100g FROM foods ORDER BY name')->fetchAll();

$errors = [];
$preview_total = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $food_ids = $_POST['food_id'] ?? [];
  $quantities = $_POST['quantity_grams'] ?? [];

  // Validierung & Kalorienberechnung
  $items = [];
  $sum = 0.0;
  for ($i = 0; $i < count($food_ids); $i++) {
    $fid = (int)$food_ids[$i];
    $q = (int)$quantities[$i];
    if ($fid && $q > 0) {
      // Wert im $foods-Array finden
      $found = null;
      foreach ($foods as $f) {
        if ((int)$f['id'] === $fid) { $found = $f; break; }
      }
      if ($found) {
        $kcal = ($q / 100.0) * (int)$found['calories_per_100g'];
        $sum += $kcal;
        $items[] = ['food_id' => $fid, 'quantity_grams' => $q];
      }
    }
  }

  if (isset($_POST['preview'])) {
    $preview_total = (int)round($sum);
  } else {
    if ($name === '') { $errors[] = 'Name des Gerichts ist Pflicht.'; }
    if (empty($items)) { $errors[] = 'Mindestens eine Zutat mit > 0 g wählen.'; }

    if (!$errors) {
      try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO meals (name, description) VALUES (?,?)');
        $stmt->execute([$name, $description ?: null]);
        $meal_id = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare('INSERT INTO meal_items (meal_id, food_id, quantity_grams) VALUES (?,?,?)');
        foreach ($items as $it) {
          $stmtItem->execute([$meal_id, $it['food_id'], $it['quantity_grams']]);
        }
        $pdo->commit();
        header('Location: /assembler.php?saved=1');
        exit;
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $errors[] = 'Speichern fehlgeschlagen.';
      }
    }
  }
}

// Für Anzeige der letzten Gerichte
$meals = $pdo->query('SELECT id, name, created_at FROM meals ORDER BY created_at DESC LIMIT 10')->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Assembler – CalTrack</title>
  <link rel="stylesheet" href="/styles.css"/>
</head>
<body>
<?php include __DIR__.'/header.php'; ?>
<main class="container" style="padding:28px 0;">
  <h1>Assembler</h1>
  <p class="info">Wähle Zutaten aus dem Katalog, gib je Gramm an, berechne die Kalorien und speichere dein Gericht.</p>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="card" style="border-left:4px solid var(--success);">
      Gericht gespeichert ✅
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
      <input class="input" name="name" placeholder="Name des Gerichts (Pflicht)"/>
      <textarea class="input" name="description" rows="2" placeholder="Beschreibung (optional)"></textarea>

      <div id="items"></div>
      <div class="row">
        <button class="button secondary" type="button" onclick="addRow()">+ Zutat hinzufügen</button>
        <button class="button" name="preview" value="1">Kalorien berechnen</button>
        <button class="button" type="submit">Gericht speichern</button>
      </div>

      <?php if ($preview_total !== null): ?>
        <p class="info">Gesamt: <b><?= (int)$preview_total ?> kcal</b> (für die eingegebenen Mengen)</p>
      <?php endif; ?>
    </form>
  </section>

  <section class="card">
    <h3>Zuletzt erstellt</h3>
    <ul>
      <?php foreach ($meals as $m): ?>
        <li class="meta"><?= htmlspecialchars($m['name']) ?> · erstellt am <?= htmlspecialchars($m['created_at']) ?></li>
      <?php endforeach; ?>
      <?php if (!$meals): ?>
        <li class="meta">Noch keine Gerichte vorhanden.</li>
      <?php endif; ?>
    </ul>
  </section>
</main>

<script>
const foods = <?php echo json_encode($foods, JSON_UNESCAPED_UNICODE); ?>;
const itemsDiv = document.getElementById('items');

function addRow() {
  const wrapper = document.createElement('div');
  wrapper.className = 'row';
  wrapper.style.marginBottom = '8px';

  const select = document.createElement('select');
  select.name = 'food_id[]';
  select.className = 'select';
  select.innerHTML = '<option value="">– Zutat wählen –</option>' +
    foods.map(f => `<option value="${f.id}">${f.name} (${f.calories_per_100g} kcal/100g)</option>`).join('');

  const qty = document.createElement('input');
  qty.type = 'number';
  qty.name = 'quantity_grams[]';
  qty.className = 'input';
  qty.min = '1';
  qty.placeholder = 'Gramm';

  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'button secondary';
  removeBtn.textContent = 'Entfernen';
  removeBtn.onclick = () => wrapper.remove();

  wrapper.appendChild(select);
  wrapper.appendChild(qty);
  wrapper.appendChild(removeBtn);
  itemsDiv.appendChild(wrapper);
}

// Starte mit zwei Reihen
addRow();
addRow();
</script>
</body>
</html>
