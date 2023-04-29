<?php
declare(strict_types=1);

namespace DR\Review\Service\IO;

class FilePathNormalizer
{
    public function normalize(string $basePath, string $filePath): string
    {
        $basePath = str_replace('\\', '/', $basePath);
        $filePath = str_replace('\\', '/', $filePath);

        if (str_starts_with($filePath, $basePath)) {
            $filePath = substr($filePath, strlen($basePath));
        }

        return ltrim($filePath, '/');
    }
}
