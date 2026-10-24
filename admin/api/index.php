<?php

// Vercel entry point. On Vercel the project folder is read-only, so Laravel keeps its
// compiled views and cache files in /tmp, and sessions and cache live in the database.

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$defaults = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/config.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/services.php',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'database',
    'CACHE_STORE' => 'database',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_SECURE_COOKIE' => 'true',
];
foreach ($defaults as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}

$storage = '/tmp/storage';
foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs', 'app/public'] as $dir) {
    if (! is_dir("{$storage}/{$dir}")) {
        @mkdir("{$storage}/{$dir}", 0775, true);
    }
}
@mkdir('/tmp/bootstrap', 0775, true);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->useStoragePath($storage);
$app->handleRequest(Request::capture());
