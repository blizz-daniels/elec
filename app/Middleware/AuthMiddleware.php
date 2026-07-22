<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;

final class AuthMiddleware
{
    public static function handle(): void
    {
        Auth::requiresLogin();
    }
}
