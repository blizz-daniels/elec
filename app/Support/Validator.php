<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        return $errors;
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function maxBytes(?array $file, int $kilobytes): bool
    {
        return $file === null || ($file['size'] ?? 0) <= $kilobytes * 1024;
    }

    public static function mimeIn(?array $file, array $allowed): bool
    {
        if ($file === null) {
            return true;
        }

        $type = mime_content_type($file['tmp_name'] ?? '');
        return in_array($type, $allowed, true);
    }
}
