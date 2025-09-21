<?php
// public/index.php
// Minimal REST microservice over GuayaquilLib (PHP SDK).
// Start locally: php -S 0.0.0.0:8080 -t public

declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';

use Laximo\Guayaquil\ServiceOem;
use Laximo\Guayaquill\ServiceAm;

// Read env
$login = getenv('LAXIMO_LOGIN') ?: '';
$pass  = getenv('LAXIMO_PASSWORD') ?: '';

// Init services
try {
  $oem = new ServiceOem($login, $pass);
  $am  = class_exists('Laximo\\Guayaquil\\ServiceAm') ? new \Laximo\Guayaquil\ServiceAm($login, $pass) : null;
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Init error: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$qs   = $_GET;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if ($uri === '/health') {
    echo json_encode(['ok' => true, 'time' => date('c')], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // === CAT ===

  // GET /cat/findVehicle?vin=WAU...
  if ($uri === '/cat/findVehicle' && $method === 'GET') {
    $vin = (string)($qs['vin'] ?? '');
    if ($vin === '') throw new Exception('vin required');
    $res = $oem->findVehicleByVin($vin);
    $v = is_array($res) ? ($res[0] ?? []) : $res;
    echo json_encode([
      'vehicleid' => (string)($v['vehicleid'] ?? $v['VehicleId'] ?? ''),
      'ssd'       => (string)($v['ssd'] ?? $v['SSD'] ?? ''),
      'catalog'   => (string)($v['catalog'] ?? $v['Catalog'] ?? ''),
      'brand'     => (string)($v['brand'] ?? $v['Brand'] ?? ''),
      'name'      => (string)($v['name'] ?? $v['Name'] ?? ''),
      'raw'       => $res
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // GET /cat/listUnits?catalog=TOYOTA00&ssd=$...&category=0&group=1
  if ($uri === '/cat/listUnits' && $method === 'GET') {
    $catalog  = (string)($qs['catalog'] ?? '');
    $category = (string)($qs['category'] ?? '0');
    $ssd      = (string)($qs['ssd'] ?? '');
    $group    = (string)($qs['group'] ?? '1');
    if ($catalog === '' || $ssd === '') throw new Exception('catalog, ssd required');
    $res = $oem->listUnits($catalog, $category, $ssd, $group);
    $arr = is_array($res) ? $res : ($res['units'] ?? []);
    $out = array_values(array_filter(array_map(function($r){
      return [
        'id'   => (string)($r['unitid'] ?? $r['id'] ?? $r['code'] ?? ''),
        'name' => (string)($r['name'] ?? $r['Name'] ?? $r['title'] ?? '')
      ];
    }, $arr), fn($x)=>$x['id'] !== ''));
    echo json_encode(['assemblies' => $out, 'raw' => $res], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // GET /cat/listDetailByUnit?catalog=TOYOTA00&ssd=$...&unitid=3423
  if ($uri === '/cat/listDetailByUnit' && $method === 'GET') {
    $catalog = (string)($qs['catalog'] ?? '');
    $ssd     = (string)($qs['ssd'] ?? '');
    $unitid  = (string)($qs['unitid'] ?? '');
    if ($catalog === '' || $ssd === '' || $unitid === '') throw new Exception('catalog, ssd, unitid required');
    $res = $oem->listDetailByUnit($catalog, $ssd, $unitid);
    $arr = is_array($res) ? $res : ($res['items'] ?? []);
    $items = array_values(array_filter(array_map(function($p){
      return [
        'oem'   => (string)($p['oem'] ?? $p['code'] ?? $p['partnumber'] ?? ''),
        'brand' => (string)($p['brand'] ?? $p['maker'] ?? ''),
        'name'  => (string)($p['name'] ?? $p['title'] ?? '')
      ];
    }, $arr), fn($p)=>$p['oem'] !== ''));
    echo json_encode(['items' => $items, 'raw' => $res], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // === DOC (Aftermarket) ===

  // GET /doc/partByOem?oem=C110&brand=VIC
  if ($uri === '/doc/partByOem' && $method === 'GET') {
    if (!$am) throw new Exception('ServiceAm is not available');
    $oemcode = (string)($qs['oem'] ?? '');
    $brand   = $qs['brand'] ?? null;
    if ($oemcode === '') throw new Exception('oem required');
    $res = $am->findOem($oemcode, $brand);
    $p = is_array($res) ? ($res[0] ?? []) : $res;
    echo json_encode([
      'oem'   => (string)($p['oem'] ?? $p['code'] ?? $oemcode),
      'brand' => (string)($p['brand'] ?? $p['maker'] ?? ''),
      'name'  => (string)($p['name'] ?? $p['title'] ?? ''),
      'raw'   => $res
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // GET /doc/crosses?oem=C110
  if ($uri === '/doc/crosses' && $method === 'GET') {
    if (!$am) throw new Exception('ServiceAm is not available');
    $oemcode = (string)($qs['oem'] ?? '');
    if ($oemcode === '') throw new Exception('oem required');
    // Некоторые версии SDK требуют флаг опций для кроссов — пример:
    $res = $am->findOem($oemcode, null /*brand*/, []);
    $arr = is_array($res) ? $res : ($res['crosses'] ?? []);
    $list = array_values(array_filter(array_map(function($c){
      return [
        'oem'   => (string)($c['oem'] ?? $c['code'] ?? ''),
        'brand' => (string)($c['brand'] ?? $c['maker'] ?? ''),
        'name'  => (string)($c['name'] ?? $c['title'] ?? '')
      ];
    }, $arr), fn($c)=>$c['oem'] !== ''));
    echo json_encode(['crosses' => $list, 'raw' => $res], JSON_UNESCAPED_UNICODE);
    exit;
  }

  http_response_code(404);
  echo json_encode(['error' => 'Not found', 'path' => $uri], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
