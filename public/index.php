<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\GuayaquilClient;

header('Content-Type: application/json; charset=utf-8');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// health
if ($path === '/health') {
    echo json_encode(['ok' => true, 'php' => PHP_VERSION]);
    exit;
}

$login = getenv('LAXIMO_LOGIN') ?: '';
$password = getenv('LAXIMO_PASSWORD') ?: '';
$client = new GuayaquilClient($login, $password);

try {
    if ($path === '/catalogs') {
        echo json_encode($client->listCatalogs(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (preg_match('~^/catalog/([A-Za-z0-9_-]+)$~', $path, $m)) {
        echo json_encode($client->getCatalogInfo($m[1]), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (preg_match('~^/catalogs-with-info/([A-Za-z0-9_-]+)$~', $path, $m)) {
        echo json_encode($client->catalogsWithInfo($m[1]), JSON_UNESCAPED_UNICODE);
        exit;
    }

    // fallback: если файл существует, отдать статикой (для php -S)
    $fullPath = __DIR__ . $path;
    if ($path !== '/' && file_exists($fullPath) && !is_dir($fullPath)) {
        return false;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'path' => $path], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

