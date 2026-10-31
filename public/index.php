<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Request::capture();

// Subdirectory installs (e.g. /commerce): route:cache breaks matching "/" because
// REQUEST_URI /commerce/ is trimmed to /commerce and pathInfo becomes "/commerce".
// Appending index.php keeps Symfony's base URL detection correct.
$scriptDir = rtrim(str_replace('\\', '/', dirname($request->server->get('SCRIPT_NAME', ''))), '/');
if ($scriptDir !== '' && $scriptDir !== '/') {
    $parts = explode('?', (string) $request->server->get('REQUEST_URI'), 2);
    $uriPath = $parts[0];

    if ($uriPath === $scriptDir || $uriPath === $scriptDir.'/') {
        $request->server->set(
            'REQUEST_URI',
            $scriptDir.'/index.php'.(isset($parts[1]) ? '?'.$parts[1] : '')
        );
    }
}

$app->handleRequest($request);
