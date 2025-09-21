<?php
// Simple router for PHP built-in server.
// Adjust to your app logic (PSR-7/your micro-framework/etc).

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Example health check
if ($path === '/health') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'php' => PHP_VERSION]);
    exit;
}

// Example endpoint placeholder
if ($path === '/api') {
    header('Content-Type: application/json');
    echo json_encode(['service' => 'laximo-php-microservice', 'status' => 'ready']);
    exit;
}

// Fallback: if file exists, serve it; otherwise 404 JSON
$fullPath = __DIR__ . $path;
if ($path !== '/' && file_exists($fullPath) && !is_dir($fullPath)) {
    return false; // let built-in server serve static
}

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Not Found', 'path' => $path]);
