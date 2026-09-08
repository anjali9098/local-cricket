<?php

// 1. Unset any invalid/empty cache env variables to prevent Laravel from requiring a directory path
$cacheKeys = [
    'APP_CONFIG_CACHE',
    'APP_SERVICES_CACHE',
    'APP_PACKAGES_CACHE',
    'APP_ROUTES_CACHE',
    'APP_EVENTS_CACHE',
];

foreach ($cacheKeys as $key) {
    putenv($key);
    unset($_ENV[$key], $_SERVER[$key]);
}

// 2. Set runtime defaults for serverless environment
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';

if (empty($_ENV['CACHE_STORE']) && empty($_SERVER['CACHE_STORE'])) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
}
if (empty($_ENV['SESSION_DRIVER']) && empty($_SERVER['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
}
if (empty($_ENV['LOG_CHANNEL']) && empty($_SERVER['LOG_CHANNEL'])) {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
}
if (empty($_ENV['QUEUE_CONNECTION']) && empty($_SERVER['QUEUE_CONNECTION'])) {
    putenv('QUEUE_CONNECTION=sync');
    $_ENV['QUEUE_CONNECTION'] = 'sync';
}
if (empty($_ENV['APP_MAINTENANCE_DRIVER']) && empty($_SERVER['APP_MAINTENANCE_DRIVER'])) {
    putenv('APP_MAINTENANCE_DRIVER=file');
    $_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
}

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// 3. Create required /tmp directories for serverless runtime
$dirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
