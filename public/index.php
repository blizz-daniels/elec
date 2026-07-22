<?php

declare(strict_types=1);

use App\Core\Application;

$debug = false;
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    $contents = file_get_contents($envFile) ?: '';
    $debug = preg_match('/^APP_DEBUG\s*=\s*(true|1|yes)$/mi', $contents) === 1;
}

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
} else {
    require dirname(__DIR__) . '/bootstrap/autoload.php';
}

try {
    $app = new Application(dirname(__DIR__));
    $app->run();
} catch (Throwable $e) {
    http_response_code(500);
    if ($debug) {
        echo '<h1>Application Error</h1>';
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo 'Internal Server Error';
    }
}
