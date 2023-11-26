<?php

declare(strict_types=1);

namespace DR\Review\Utility;

class FileUtil
{
    public static function getMimeType(string $filePath): ?string
    {
        static $mimes = null;
        $mimes ??= require __DIR__ . '/../../resources/mimes/mime-types.php';

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return $mimes[$extension] ?? null;
    }

    public static function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }
}
