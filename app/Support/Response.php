<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    public static function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $viewFile = Config::basePath('app/Views/' . $template . '.php');
        if (!file_exists($viewFile)) {
            self::notFound('View not found');
            return;
        }

        require Config::basePath('app/Views/layouts/app.php');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public static function notFound(string $message = 'Not found'): void
    {
        http_response_code(404);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
