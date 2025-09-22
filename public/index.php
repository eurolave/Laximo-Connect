<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// ---- bootstrap guayaquillib (усиленный) ----
$libPath = null;
if (class_exists('\Composer\InstalledVersions')
    && \Composer\InstalledVersions::isInstalled('laximo/guayaquillib')) {
    $libPath = \Composer\InstalledVersions::getInstallPath('laximo/guayaquillib');
}

// fallback, если Composer не вернул путь
if (!$libPath) {
    $fallback = __DIR__ . '/../vendor/laximo/guayaquillib';
    if (is_dir($fallback)) {
        $libPath = $fallback;
    }
}

if ($libPath) {
    // 1) Попробовать «точки входа», если вдруг есть
    foreach (['/src/autoload.php', '/src/guayaquillib.php'] as $entry) {
        $p = $libPath . $entry;
        if (is_file($p)) {
            require_once $p;
        }
    }

    // 2) Явно подключить ключевые файлы классов (чаще всего так называются)
    foreach (['/src/ServiceOem.php', '/src/Oem.php', '/src/ServiceAm.php', '/src/Am.php'] as $entry) {
        $p = $libPath . $entry;
        if (is_file($p)) {
            require_once $p;
        }
    }

    // 3) Подстраховка: подключить все .php в src/
    $srcDir = $libPath . '/src';
    if (is_dir($srcDir)) {
        foreach (glob($srcDir . '/*.php') as $file) {
            require_once $file;
        }
    }
}
// ---- end bootstrap --------------------------

// -------------------------------------------------------------------


use App\GuayaquilClient;

header('Content-Type: application/json; charset=utf-8');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// ===== TEMP: диагностика Composer/классов =====
if ($path === '/_diag') {
    require __DIR__ . '/../vendor/autoload.php';
    $data = [
        'vendor_autoload' => file_exists(__DIR__ . '/../vendor/autoload.php'),
        'ServiceOem_class' => class_exists('\ServiceOem'),
        'Oem_class' => class_exists('\Oem'),
        'composer_class' => class_exists('\Composer\InstalledVersions'),
    ];
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($path === '/_diag/packages') {
    require __DIR__ . '/../vendor/autoload.php';
    if (class_exists('\Composer\InstalledVersions')) {
        $installed = \Composer\InstalledVersions::getInstalledPackages();
        $hasGuayaquil = \Composer\InstalledVersions::isInstalled('laximo/guayaquillib');
        $version = $hasGuayaquil ? \Composer\InstalledVersions::getPrettyVersion('laximo/guayaquillib') : null;
        $data = [
            'has_laximo_guayaquillib' => $hasGuayaquil,
            'laximo_guayaquillib_version' => $version,
            'packages_count' => count($installed),
        ];
    } else {
        $data = ['error' => 'Composer\\InstalledVersions недоступен'];
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

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

