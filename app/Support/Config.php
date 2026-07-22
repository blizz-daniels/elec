<?php

declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/\\');
    }

    public static function basePath(string $path = ''): string
    {
        $full = self::$basePath . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $full);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $value === false || $value === null || $value === '' ? $default : $value;
    }
}
