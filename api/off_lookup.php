<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '' || mb_strlen($q) < 2) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Bitte mindestens 2 Zeichen eingeben.'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Cache
$cacheDir = __DIR__ . '/_cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);

$cacheKey   = md5(mb_strtolower($q));
$cacheFile  = $cacheDir . '/' . $cacheKey . '.json';
$ttlSeconds = 60 * 60 * 24;

if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttlSeconds)) {
  echo file_get_contents($cacheFile);
  exit;
}

$params = http_build_query([
  'search_terms'   => $q,
  'search_simple'  => 1,
  'action'         => 'process',
  'json'           => 1,
  'page_size'      => 12,
  'sort_by'        => 'unique_scans_n',
  'fields'         => 'product_name,brands,code,nutriments',
]);

$url = "https://world.openfoodfacts.org/cgi/search.pl?$params";

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 20,
  CURLOPT_CONNECTTIMEOUT => 10,
  CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
  CURLOPT_HTTPHEADER => [
    'User-Agent: CalTrack/1.0 (mael@example.com)', // anpassen
    'Accept: application/json',
  ],
]);

$raw = curl_exec($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
$errno = curl_errno($ch);

// SSL/CA-Fallback (XAMPP/Windows häufig)
if ($raw === false && in_array($errno, [60, 77, 51, 53], true)) {
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

  $raw = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  $errno = curl_errno($ch);
}

curl_close($ch);

if ($raw === false || $http >= 400) {
  http_response_code(502);
  echo json_encode([
    'ok' => false,
    'error' => 'OFF Anfrage fehlgeschlagen.',
    'details' => $err ?: ("HTTP $http"),
    'errno' => $errno,
    'url' => $url
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$data = json_decode($raw, true);
if (!is_array($data) || !isset($data['products']) || !is_array($data['products'])) {
  http_response_code(502);
  echo json_encode([
    'ok' => false,
    'error' => 'Unerwartete OFF Antwort.',
    'url' => $url
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// erster Treffer mit kcal/100g
$best = null;
foreach ($data['products'] as $p) {
  $nut = $p['nutriments'] ?? [];
  $kcal100 = $nut['energy-kcal_100g'] ?? null;
  if ($kcal100 === null || $kcal100 === '' || !is_numeric($kcal100)) continue;
  $best = $p;
  break;
}

if ($best === null) {
  $out = json_encode(['ok' => true, 'found' => false], JSON_UNESCAPED_UNICODE);
  @file_put_contents($cacheFile, $out);
  echo $out;
  exit;
}

$nut = $best['nutriments'] ?? [];
$outArr = [
  'ok' => true,
  'found' => true,
  'item' => [
    'name' => (string)($best['product_name'] ?? ''),
    'brands' => (string)($best['brands'] ?? ''),
    'code' => (string)($best['code'] ?? ''),
    'kcal_100g' => isset($nut['energy-kcal_100g']) ? (float)$nut['energy-kcal_100g'] : null,
  ],
];

$out = json_encode($outArr, JSON_UNESCAPED_UNICODE);
@file_put_contents($cacheFile, $out);
echo $out;
