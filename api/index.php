<?php

// Ensure essential environment variables have fallback defaults
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

// Ensure /tmp folders exist for serverless environment
$dirs = [
    '/tmp/storage/app/public',
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

require __DIR__ . '/../public/index.php';
