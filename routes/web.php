<?php

declare(strict_types=1);

return [
    'GET /' => [App\Controllers\HomeController::class, 'index'],
    'GET /login' => [App\Controllers\AuthController::class, 'showLogin'],
    'POST /login' => [App\Controllers\AuthController::class, 'login'],
    'GET /register' => [App\Controllers\AuthController::class, 'showRegister'],
    'POST /register' => [App\Controllers\AuthController::class, 'register'],
];
