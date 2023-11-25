<?php

declare(strict_types=1);

namespace DR\Review\Utility;

class FileUtil
{
    public static function getMimeType(string $filePath): ?string
    {
        if (preg_match('/\.png$/i', $filePath)) {
            return 'image/png';
        }
        if (preg_match('/\.jpe?g$/i', $filePath)) {
            return 'image/jpg';
        }
        if (preg_match('/\.gif$/i', $filePath)) {
            return 'image/gif';
        }
        if (preg_match('/\.svg$/i', $filePath)) {
            return 'image/svg+xml';
        }
        if (preg_match('/\.pdf$/i', $filePath)) {
            return 'application/pdf';
        }
        if (preg_match('/\.md?$/i', $filePath)) {
            return 'text/markdown';
        }

        return null;
    }

    public static function isBinary(string $mimeType): bool
    {
        return in_array(
            $mimeType,
            [
                'image/png',
                'image/jpg',
                'image/gif',
                'application/pdf',
            ],
            true
        );
    }
}
