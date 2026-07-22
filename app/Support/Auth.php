<?php

declare(strict_types=1);

namespace App\Support;

final class Auth
{
    public static function user(): ?array
    {
        return Session::get('auth_user');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        $user = self::user();

        return $user['role_slug'] ?? $user['role'] ?? null;
    }

    public static function requiresLogin(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    public static function requiresRole(array|string $roles): void
    {
        $roles = is_array($roles) ? $roles : [$roles];
        if (!self::check() || !in_array((string) self::role(), $roles, true)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
