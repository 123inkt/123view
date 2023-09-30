<?php
declare(strict_types=1);

namespace DR\Review\Service\IO;

class FilePathNormalizer
{
    public function normalize(string $basePath, string $subDirectory, string $filePath): string
    {
        $basePath     = str_replace('\\', '/', $basePath);
        $filePath     = str_replace('\\', '/', $filePath);
        $subDirectory = trim(str_replace('\\', '/', $subDirectory), '/');

        // remove base path from file path
        if (str_starts_with($filePath, $basePath)) {
            $filePath = substr($filePath, strlen($basePath));
        }

        $filePath = ltrim($filePath, '/');

        // append sub directory to file path
        if ($subDirectory !== '' && $filePath !== '') {
            $filePath = $subDirectory . '/' . $filePath;
        }

        return $filePath;
    }
}
