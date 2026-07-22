<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;

final class RoleMiddleware
{
    public static function handle(array|string $roles): void
    {
        Auth::requiresRole($roles);
    }
}
