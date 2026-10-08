<?php

use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$storagePath = sys_get_temp_dir().'/datadog-license-generator';
$storageDirectories = [
    'app/private',
    'app/public',
    'framework/cache/data',
    'framework/sessions',
    'framework/views',
    'logs',
];

foreach ($storageDirectories as $directory) {
    $path = $storagePath.'/'.$directory;

    if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
        throw new RuntimeException("Unable to create temporary storage directory: {$path}");
    }
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->useStoragePath($storagePath);
$app->handleRequest(Request::capture());
