<?php

ini_set('display_errors', '0');
error_reporting(0);

// 1. Direct Static File Serving for Vercel Serverless Runtime
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$publicBase = realpath(__DIR__ . '/../public');
$publicFile = ($publicBase && $uri !== '/') ? realpath($publicBase . $uri) : false;

// If not found in public and starts with /storage/, check storage/app/public
if ((!$publicFile || !is_file($publicFile)) && str_starts_with($uri, '/storage/')) {
    $storageBase = realpath(__DIR__ . '/../storage/app/public');
    if ($storageBase) {
        $storageRel = substr($uri, 8); // e.g. /stories/xxx.png
        $candidate = realpath($storageBase . $storageRel);
        if ($candidate && str_starts_with($candidate, $storageBase) && is_file($candidate)) {
            $publicFile = $candidate;
            $publicBase = $storageBase;
        }
    }
}

if ($publicFile && $publicBase && str_starts_with($publicFile, $publicBase) && is_file($publicFile) && $uri !== '/' && !str_ends_with($publicFile, 'index.php')) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'avif'  => 'image/avif',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'json'  => 'application/json',
        'txt'   => 'text/plain',
    ];
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Content-Length: ' . filesize($publicFile));
    readfile($publicFile);
    exit;
}

// 2. Ensure valid file paths for cache files so Laravel never tries to require the root directory
putenv('APP_CONFIG_CACHE=/tmp/storage/framework/cache/config.php');
$_ENV['APP_CONFIG_CACHE'] = '/tmp/storage/framework/cache/config.php';
$_SERVER['APP_CONFIG_CACHE'] = '/tmp/storage/framework/cache/config.php';

putenv('APP_SERVICES_CACHE=/tmp/storage/framework/cache/services.php');
$_ENV['APP_SERVICES_CACHE'] = '/tmp/storage/framework/cache/services.php';
$_SERVER['APP_SERVICES_CACHE'] = '/tmp/storage/framework/cache/services.php';

putenv('APP_PACKAGES_CACHE=/tmp/storage/framework/cache/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/storage/framework/cache/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = '/tmp/storage/framework/cache/packages.php';

putenv('APP_ROUTES_CACHE=/tmp/storage/framework/cache/routes.php');
$_ENV['APP_ROUTES_CACHE'] = '/tmp/storage/framework/cache/routes.php';
$_SERVER['APP_ROUTES_CACHE'] = '/tmp/storage/framework/cache/routes.php';

putenv('APP_EVENTS_CACHE=/tmp/storage/framework/cache/events.php');
$_ENV['APP_EVENTS_CACHE'] = '/tmp/storage/framework/cache/events.php';
$_SERVER['APP_EVENTS_CACHE'] = '/tmp/storage/framework/cache/events.php';

// 3. Set serverless storage and driver defaults
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Always enforce HTTPS on Vercel
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';

putenv('LARAVEL_STORAGE_PATH=/tmp/storage');
$_ENV['LARAVEL_STORAGE_PATH'] = '/tmp/storage';
$_SERVER['LARAVEL_STORAGE_PATH'] = '/tmp/storage';

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

putenv('APP_NAME=CricketKaScore');
$_ENV['APP_NAME'] = 'CricketKaScore';
$_SERVER['APP_NAME'] = 'CricketKaScore';

putenv('SESSION_LIFETIME=1440');
$_ENV['SESSION_LIFETIME'] = '1440';
$_SERVER['SESSION_LIFETIME'] = '1440';

putenv('SESSION_COOKIE=cricketkascore_session');
$_ENV['SESSION_COOKIE'] = 'cricketkascore_session';
$_SERVER['SESSION_COOKIE'] = 'cricketkascore_session';

putenv('BCRYPT_ROUNDS=12');
$_ENV['BCRYPT_ROUNDS'] = '12';
$_SERVER['BCRYPT_ROUNDS'] = '12';

if (empty($_ENV['APP_KEY']) && empty($_SERVER['APP_KEY'])) {
    putenv('APP_KEY=base64:cc/wjbEfRbg0NQQuu+FH/uRy9X8Rev5jAjWk9TFX5jE=');
    $_ENV['APP_KEY'] = 'base64:cc/wjbEfRbg0NQQuu+FH/uRy9X8Rev5jAjWk9TFX5jE=';
    $_SERVER['APP_KEY'] = 'base64:cc/wjbEfRbg0NQQuu+FH/uRy9X8Rev5jAjWk9TFX5jE=';
}

if (!empty($_SERVER['HTTP_HOST'])) {
    putenv('APP_URL=https://' . $_SERVER['HTTP_HOST']);
    $_ENV['APP_URL'] = 'https://' . $_SERVER['HTTP_HOST'];
    $_SERVER['APP_URL'] = 'https://' . $_SERVER['HTTP_HOST'];
}

if (empty($_ENV['CRICKETDATA_API_KEY']) && empty($_SERVER['CRICKETDATA_API_KEY'])) {
    putenv('CRICKETDATA_API_KEY=c7d0228c-6e2b-49f4-a27d-7fe329dc9d39');
    $_ENV['CRICKETDATA_API_KEY'] = 'c7d0228c-6e2b-49f4-a27d-7fe329dc9d39';
    $_SERVER['CRICKETDATA_API_KEY'] = 'c7d0228c-6e2b-49f4-a27d-7fe329dc9d39';
}

if (empty($_ENV['DB_CONNECTION']) && empty($_SERVER['DB_CONNECTION'])) {
    putenv('DB_CONNECTION=mysql');
    $_ENV['DB_CONNECTION'] = 'mysql';
    $_SERVER['DB_CONNECTION'] = 'mysql';
}
if (empty($_ENV['CACHE_STORE']) && empty($_SERVER['CACHE_STORE'])) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
    $_SERVER['CACHE_STORE'] = 'array';
}
if (empty($_ENV['SESSION_DRIVER']) && empty($_SERVER['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=database');
    $_ENV['SESSION_DRIVER'] = 'database';
    $_SERVER['SESSION_DRIVER'] = 'database';
}
if (empty($_ENV['SESSION_SECURE_COOKIE']) && empty($_SERVER['SESSION_SECURE_COOKIE'])) {
    putenv('SESSION_SECURE_COOKIE=true');
    $_ENV['SESSION_SECURE_COOKIE'] = 'true';
    $_SERVER['SESSION_SECURE_COOKIE'] = 'true';
}
if (empty($_ENV['SESSION_SAME_SITE']) && empty($_SERVER['SESSION_SAME_SITE'])) {
    putenv('SESSION_SAME_SITE=lax');
    $_ENV['SESSION_SAME_SITE'] = 'lax';
    $_SERVER['SESSION_SAME_SITE'] = 'lax';
}
if (empty($_ENV['LOG_CHANNEL']) && empty($_SERVER['LOG_CHANNEL'])) {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
    $_SERVER['LOG_CHANNEL'] = 'stderr';
}
if (empty($_ENV['QUEUE_CONNECTION']) && empty($_SERVER['QUEUE_CONNECTION'])) {
    putenv('QUEUE_CONNECTION=sync');
    $_ENV['QUEUE_CONNECTION'] = 'sync';
    $_SERVER['QUEUE_CONNECTION'] = 'sync';
}
if (empty($_ENV['APP_MAINTENANCE_DRIVER']) && empty($_SERVER['APP_MAINTENANCE_DRIVER'])) {
    putenv('APP_MAINTENANCE_DRIVER=file');
    $_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
    $_SERVER['APP_MAINTENANCE_DRIVER'] = 'file';
}

// 4. Create required /tmp directories for serverless runtime
$dirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Diagnostic Error</title></head><body style="background:#0f172a;color:#f8fafc;font-family:sans-serif;padding:30px;">';
    echo '<h1 style="color:#ef4444;">Serverless PHP Error</h1>';
    echo '<h3 style="color:#f59e0b;">' . htmlspecialchars($e->getMessage()) . '</h3>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<pre style="background:#1e293b;padding:15px;border-radius:8px;overflow-x:auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
}
