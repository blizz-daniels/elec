<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Config;
use App\Support\Response;
use App\Support\Session;

if (!function_exists('app')) {
    function app(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) Config::get('APP_URL', ''), '/');
        $path = ltrim($path, '/');

        if ($path === '' || $path === '#') {
            return $base . '/index.php';
        }

        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'public/assets/') || str_starts_with($path, 'uploads/')) {
            return $base . '/' . $path;
        }

        return $base . '/index.php/' . $path;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = [], int $status = 200): void
    {
        Response::view($template, $data, $status);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        Response::redirect($path);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }

        return $token;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::get('auth_user');
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return auth_user() !== null;
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string
    {
        return auth_user()['role'] ?? null;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::get('old.' . $key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if (func_num_args() === 2) {
            Session::set('flash.' . $key, $value);
            return null;
        }

        $value = Session::get('flash.' . $key);
        Session::forget('flash.' . $key);
        return $value;
    }
}
