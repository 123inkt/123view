<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;

class AiCodeReviewFileFilter
{
    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];
    private const int   MAX_FILE_LENGTH       = 500;

    /**
     * Summary
     *  - exclude files with baseline in their file path
     *  - exclude files with disallowed extensions
     *  - exclude binary files
     *  - exclude deleted files
     *  - exclude files with more than 500 lines
     */
    public function __invoke(DiffFile $file): bool
    {
        if (str_contains($file->getPathname(), 'baseline')) {
            return false;
        }
        if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::DISALLOWED_EXTENSIONS, true)) {
            return false;
        }
        if ($file->binary || $file->isDeleted()) {
            return false;
        }

        return count($file->getLines()) <= self::MAX_FILE_LENGTH;
    }
}
