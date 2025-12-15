<?php
// upload_image.php
// Akzeptiert: POST multipart/form-data mit Feld 'file'

header('Content-Type: application/json; charset=utf-8');

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']); exit;
  }
  if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Keine Datei oder Upload-Fehler']); exit;
  }

  // Max ~5 MB
  if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    http_response_code(413);
    echo json_encode(['ok' => false, 'error' => 'Datei zu groß (max. 5 MB)']); exit;
  }

  // MIME-Typ prüfen
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($_FILES['file']['tmp_name']);
  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
  ];
  if (!isset($allowed[$mime])) {
    http_response_code(415);
    echo json_encode(['ok' => false, 'error' => 'Nur JPG, PNG, WEBP, GIF erlaubt']); exit;
  }

  // Uploads-Ordner sicherstellen
  $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0775, true)) {
      throw new Exception('Konnte Upload-Ordner nicht erstellen');
    }
  }

  // Eindeutiger Dateiname
  $ext = $allowed[$mime];
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $name;

  if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    throw new Exception('Speichern fehlgeschlagen');
  }

  // Öffentlicher Pfad für die DB
  $publicPath = '/uploads/' . $name;

  echo json_encode(['ok' => true, 'path' => $publicPath]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Serverfehler: ' . $e->getMessage()]);
}
