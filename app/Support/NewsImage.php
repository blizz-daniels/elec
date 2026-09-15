<?php

declare(strict_types=1);

namespace App\Support;

final class NewsImage
{
    private const MAX_WIDTH = 1280;
    private const ASPECT_RATIO = 16 / 9;

    public static function store(array $file): string
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('News image processing requires the PHP GD extension.');
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $imageInfo = $temporaryPath !== '' ? @getimagesize($temporaryPath) : false;
        if ($imageInfo === false) {
            throw new \RuntimeException('The uploaded file is not a valid image.');
        }

        $type = (int) ($imageInfo[2] ?? 0);
        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($temporaryPath),
            IMAGETYPE_PNG => @imagecreatefrompng($temporaryPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($temporaryPath) : false,
            default => false,
        };
        if ($source === false) {
            throw new \RuntimeException('Use a JPG, PNG, or WebP image for the news photo.');
        }

        $source = self::orientJpeg($source, $temporaryPath, $type);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        [$sourceX, $sourceY, $cropWidth, $cropHeight] = self::cropArea($sourceWidth, $sourceHeight);

        $targetWidth = min(self::MAX_WIDTH, $cropWidth);
        $targetHeight = max(1, (int) round($targetWidth / self::ASPECT_RATIO));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );

        $directory = Config::basePath('public/uploads/news');
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            imagedestroy($canvas);
            imagedestroy($source);
            throw new \RuntimeException('The news image folder could not be created.');
        }

        $extension = match ($type) {
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };
        $relativePath = 'uploads/news/' . bin2hex(random_bytes(16)) . '.' . $extension;
        $target = Config::basePath('public/' . $relativePath);

        try {
            $saved = match ($type) {
                IMAGETYPE_PNG => imagepng($canvas, $target, 6),
                IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($canvas, $target, 82),
                default => imagejpeg($canvas, $target, 84),
            };
        } finally {
            imagedestroy($canvas);
            imagedestroy($source);
        }

        if (!$saved) {
            throw new \RuntimeException('The news image could not be saved.');
        }

        return $relativePath;
    }

    public static function remove(?string $relativePath): void
    {
        $relativePath = trim((string) $relativePath);
        if ($relativePath === '' || !str_starts_with($relativePath, 'uploads/news/')) {
            return;
        }

        $path = Config::basePath('public/' . $relativePath);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function cropArea(int $width, int $height): array
    {
        $sourceAspect = $width / max(1, $height);
        if ($sourceAspect > self::ASPECT_RATIO) {
            $cropHeight = $height;
            $cropWidth = (int) round($height * self::ASPECT_RATIO);
            return [(int) floor(($width - $cropWidth) / 2), 0, $cropWidth, $cropHeight];
        }

        $cropWidth = $width;
        $cropHeight = (int) round($width / self::ASPECT_RATIO);
        return [0, (int) floor(($height - $cropHeight) / 2), $cropWidth, $cropHeight];
    }

    private static function orientJpeg(\GdImage $image, string $path, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
            return $image;
        }

        $metadata = @exif_read_data($path);
        $orientation = is_array($metadata) ? (int) ($metadata['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated !== false) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }
}