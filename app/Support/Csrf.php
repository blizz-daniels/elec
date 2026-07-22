<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public static function token(): string
    {
        return csrf_token();
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get('csrf_token');

        return is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
    }
}
