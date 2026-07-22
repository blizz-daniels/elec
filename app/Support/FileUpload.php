<?php

declare(strict_types=1);

namespace App\Support;

final class FileUpload
{
    public static function store(array $file, string $directory): string
    {
        $baseDir = Config::basePath($directory);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $name = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
        $target = $baseDir . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file((string) ($file['tmp_name'] ?? ''), $target)) {
            throw new \RuntimeException('Failed to upload file.');
        }

        return trim($directory, '/\\') . '/' . $name;
    }
}
