<?php
require __DIR__.'/db_connect.php';

$errors = [];
$notice = '';

// ADD / UPDATE / DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $image_url = trim($_POST['image_url'] ?? ''); // kommt aus Upload oder URL
    $description = trim($_POST['description'] ?? '');
    $cal100 = intval($_POST['calories_per_100g'] ?? 0);

    if ($name === '' || $cal100 <= 0) $errors[] = 'Name und positive kcal/100g sind Pflicht.';
    if ($image_url !== '' && (str_starts_with($image_url, 'http://') || str_starts_with($image_url, 'https://'))) {
      if (!filter_var($image_url, FILTER_VALIDATE_URL)) $errors[] = 'Bild-URL ist ungültig.';
    }
    if (!$errors) {
      $stmt = $pdo->prepare('INSERT INTO foods (name, image_url, description, calories_per_100g) VALUES (?,?,?,?)');
      $stmt->execute([$name, $image_url ?: null, $description ?: null, $cal100]);
      $notice = 'Hinzugefügt ✅';
    }
  }

  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cal100 = intval($_POST['calories_per_100g'] ?? 0);

    if ($id <= 0) $errors[] = 'Ungültige ID.';
    if ($name === '' || $cal100 <= 0) $errors[] = 'Name und positive kcal/100g sind Pflicht.';
    if ($image_url !== '' && (str_starts_with($image_url, 'http://') || str_starts_with($image_url, 'https://'))) {
      if (!filter_var($image_url, FILTER_VALIDATE_URL)) $errors[] = 'Bild-URL ist ungültig.';
    }
    if (!$errors) {
      $stmt = $pdo->prepare('UPDATE foods SET name=?, image_url=?, description=?, calories_per_100g=? WHERE id=?');
      $stmt->execute([$name, $image_url ?: null, $description ?: null, $cal100, $id]);
      $notice = 'Aktualisiert ✅';
    }
  }

  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      $errors[] = 'Ungültige ID.';
    } else {
      try {
        $stmt = $pdo->prepare('DELETE FROM foods WHERE id=?');
        $stmt->execute([$id]);
        $notice = 'Gelöscht 🗑️';
      } catch (Throwable $e) {
        $errors[] = 'Dieses Lebensmittel kann nicht gelöscht werden, da es noch in einem Gericht verwendet wird. '
                   .'Bitte entferne es zuerst aus allen Gerichten und versuche es danach erneut.';
      }
    }
  }
}

// Liste laden
$foods = $pdo->query('SELECT * FROM foods ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Katalog – CalTrack</title>
  <link rel="stylesheet" href="/styles.css"/>
</head>
<body>
<?php include __DIR__.'/header.php'; ?>
<main class="container" style="padding:28px 0;">
  <div class="row" style="justify-content: space-between; align-items: center;">
    <h1 style="margin:6px 0 14px;">Katalog</h1>
    <div class="row" style="gap:10px;">
      <?php if ($notice): ?><span class="badge"><?= htmlspecialchars($notice) ?></span><?php endif; ?>
      <button class="button sm" type="button" id="openAddBtn">+ Lebensmittel</button>
    </div>
  </div>
  <p class="info">Kalorien beziehen sich auf <b>100 g</b>. Bild-Upload per Drag & Drop oder „Datei wählen“. Placeholder bei Fehler.</p>

  <?php if ($errors): ?>
    <div class="card" style="border-left:4px solid var(--danger);">
      <strong>Fehler:</strong>
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <section class="card" style="margin-top:18px;">
    <table class="table">
      <thead>
        <tr>
          <th style="width:72px;">Bild</th>
          <th>Name</th>
          <th>Beschreibung</th>
          <th style="width:140px;">kcal / 100g</th>
          <th style="width:120px; text-align:right;">Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($foods as $f): ?>
          <tr data-id="<?= (int)$f['id'] ?>"
              data-name="<?= htmlspecialchars($f['name']) ?>"
              data-kcal="<?= (int)$f['calories_per_100g'] ?>"
              data-img="<?= htmlspecialchars($f['image_url'] ?? '') ?>"
              data-desc="<?= htmlspecialchars($f['description'] ?? '') ?>">
            <td>
              <img
                src="<?= htmlspecialchars($f['image_url'] ?: '/images/placeholder.png') ?>"
                alt=""
                onerror="this.onerror=null;this.src='/images/placeholder.png';"
                style="width:60px;height:60px;object-fit:cover;border-radius:12px;"
              />
            </td>
            <td><strong><?= htmlspecialchars($f['name']) ?></strong></td>
            <td><?= nl2br(htmlspecialchars($f['description'] ?? '')) ?></td>
            <td><?= (int)$f['calories_per_100g'] ?></td>
            <td class="actions-cell">
              <button class="iconbtn edit-btn" title="Bearbeiten" aria-label="Bearbeiten">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.42l-2.34-2.34a1 1 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/></svg>
              </button>
              <form method="post" style="display:inline" onsubmit="return confirm('Wirklich löschen?');">
                <input type="hidden" name="action" value="delete"/>
                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>"/>
                <button class="iconbtn danger" title="Löschen" aria-label="Löschen">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$foods): ?>
          <tr><td colspan="5" class="meta">Noch keine Einträge.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
</main>

<!-- ADD Modal -->
<div class="modal-backdrop" id="addModal">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="addTitle">
    <div class="modal-header">
      <div class="modal-title" id="addTitle">Lebensmittel hinzufügen</div>
    </div>
    <form method="post" class="form" autocomplete="off">
      <input type="hidden" name="action" value="add"/>
      <div class="row">
        <input class="input" name="name" placeholder="Name (Pflicht)" required autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"/>
        <input class="input" type="number" name="calories_per_100g" placeholder="kcal / 100g (Pflicht)" min="1" required inputmode="numeric" onwheel="this.blur()" autocomplete="off"/>
      </div>
      <textarea class="input" name="description" rows="3" placeholder="Beschreibung (optional)" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"></textarea>

      <!-- Upload + Hidden image_url -->
      <input type="hidden" name="image_url" id="add_img"/>
      <div class="dropzone" id="addDropzone">
        <span class="cloud" aria-hidden="true"></span>
        <div class="hint">Datei hier ablegen oder Datei wählen</div>
        <button class="button secondary sm" type="button" id="addPickBtn">Datei wählen</button>
        <input type="file" id="addFileInput" accept="image/*" style="display:none"/>
        <div class="hint" id="addFileName" style="display:none;"></div>
      </div>

      <div class="row" style="justify-content:flex-end; gap:10px;">
        <button class="button secondary" type="button" data-close="#addModal">Abbrechen</button>
        <button class="button" type="submit">Hinzufügen</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT Modal -->
<div class="modal-backdrop" id="editModal">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
    <div class="modal-header">
      <div class="modal-title" id="editTitle">Lebensmittel bearbeiten</div>
    </div>
    <form method="post" class="form" id="editForm" autocomplete="off">
      <input type="hidden" name="action" value="update"/>
      <input type="hidden" name="id" id="edit_id"/>
      <div class="row">
        <input class="input" name="name" id="edit_name" placeholder="Name (Pflicht)" required autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"/>
        <input class="input" type="number" name="calories_per_100g" id="edit_kcal" placeholder="kcal / 100g (Pflicht)" min="1" required inputmode="numeric" onwheel="this.blur()" autocomplete="off"/>
      </div>
      <textarea class="input" name="description" id="edit_desc" rows="3" placeholder="Beschreibung (optional)" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"></textarea>

      <!-- Upload + Hidden image_url -->
      <input type="hidden" name="image_url" id="edit_img"/>
      <div class="dropzone" id="editDropzone">
        <span class="cloud" aria-hidden="true"></span>
        <div class="hint">Datei hier ablegen oder Datei wählen</div>
        <button class="button secondary sm" type="button" id="editPickBtn">Datei wählen</button>
        <input type="file" id="editFileInput" accept="image/*" style="display:none"/>
        <div class="hint" id="editFileName" style="display:none;"></div>
      </div>

      <div class="row" style="justify-content:flex-end; gap:10px;">
        <button class="button secondary" type="button" data-close="#editModal">Abbrechen</button>
        <button class="button" type="submit">Speichern</button>
      </div>
    </form>
  </div>
</div>

<script>
// Modal Helpers
function openModal(id) { const el = document.querySelector(id); if (!el) return; el.classList.add('modal-visible'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { const el = document.querySelector(id); if (!el) return; el.classList.remove('modal-visible'); document.body.style.overflow = ''; }

// Open/Close Add
document.getElementById('openAddBtn').addEventListener('click', () => openModal('#addModal'));
document.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(btn.getAttribute('data-close'))));
['addModal','editModal'].forEach(mid => {
  const m = document.getElementById(mid);
  m.addEventListener('click', (e) => { if (e.target === m) closeModal('#'+mid); });
});
window.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeModal('#addModal'); closeModal('#editModal'); } });

// Edit Buttons
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const tr = e.currentTarget.closest('tr');
    document.getElementById('edit_id').value = tr.dataset.id;
    document.getElementById('edit_name').value = tr.dataset.name;
    document.getElementById('edit_kcal').value = tr.dataset.kcal;
    document.getElementById('edit_desc').value = tr.dataset.desc || '';
    document.getElementById('edit_img').value = tr.dataset.img || '';
    openModal('#editModal');
    setTimeout(()=>document.getElementById('edit_name').focus(), 30);
  });
});

// ===== Upload-Helper (wiederverwendbar) =====
function setupUpload(dropId, fileInputId, pickBtnId, fileNameId, hiddenFieldId) {
  const dz = document.getElementById(dropId);
  const fi = document.getElementById(fileInputId);
  const pick = document.getElementById(pickBtnId);
  const nameEl = document.getElementById(fileNameId);
  const hidden = document.getElementById(hiddenFieldId);

  function showName(n){ nameEl.textContent = n + ' ✓ hochgeladen'; nameEl.style.display='block'; }
  function upload(file){
    const fd = new FormData(); fd.append('file', file);
    return fetch('/upload_image.php', { method:'POST', body: fd })
      .then(async (res)=>{ const data = await res.json().catch(()=>({})); if(!res.ok||!data.ok) throw new Error(data.error||'Upload fehlgeschlagen'); return data.path; });
  }
  function handle(files){ const f = files && files[0]; if(!f) return;
    upload(f).then(p => { hidden.value = p; showName(f.name); }).catch(err => alert(err.message||'Upload fehlgeschlagen.'));
  }
  ['dragenter','dragover'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dz.classList.add('dragover'); }));
  ['dragleave','drop'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dz.classList.remove('dragover'); }));
  dz.addEventListener('drop', e => handle(e.dataTransfer.files));
  pick.addEventListener('click', () => fi.click());
  fi.addEventListener('change', () => handle(fi.files));
}

// Add- und Edit-Upload aktivieren
setupUpload('addDropzone', 'addFileInput', 'addPickBtn', 'addFileName', 'add_img');
setupUpload('editDropzone', 'editFileInput', 'editPickBtn', 'editFileName', 'edit_img');

// Sicherheit: Verhindere, dass Drop ausserhalb Modal die Seite ersetzt
window.addEventListener('dragover', e => e.preventDefault());
window.addEventListener('drop', e => { if (!document.querySelector('.modal-visible')) e.preventDefault(); });
</script>
</body>
</html>
