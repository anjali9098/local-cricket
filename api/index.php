<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure valid file paths for cache files so Laravel never tries to require the root directory
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

// Set serverless storage and driver defaults
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

putenv('LARAVEL_STORAGE_PATH=/tmp/storage');
$_ENV['LARAVEL_STORAGE_PATH'] = '/tmp/storage';
$_SERVER['LARAVEL_STORAGE_PATH'] = '/tmp/storage';

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

if (empty($_ENV['CACHE_STORE']) && empty($_SERVER['CACHE_STORE'])) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
    $_SERVER['CACHE_STORE'] = 'array';
}
if (empty($_ENV['SESSION_DRIVER']) && empty($_SERVER['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_SERVER['SESSION_DRIVER'] = 'cookie';
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

// Create required /tmp directories for serverless runtime
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
