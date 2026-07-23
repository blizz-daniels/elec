<?php

declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static string $basePath = '';
    private static ?array $config = null;

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/\\');
        self::$config = null;
    }

    public static function basePath(string $path = ''): string
    {
        $full = self::$basePath . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $full);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $config = self::loadConfig();
        if (array_key_exists($key, $config) && $config[$key] !== null && $config[$key] !== '') {
            return $config[$key];
        }

        return $default;
    }

    private static function loadConfig(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $configFile = self::basePath('config.php');
        if (!is_file($configFile)) {
            self::$config = [];
            return self::$config;
        }

        $config = require $configFile;
        self::$config = is_array($config) ? $config : [];

        return self::$config;
    }
}
