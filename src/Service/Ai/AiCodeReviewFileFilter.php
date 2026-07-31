<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;

class AiCodeReviewFileFilter
{
    /** Files with more changed lines than this are skipped as a coverage gap. */
    public const int MAX_FILE_LINES = 500;

    /** Files whose raw diff is larger than this (in characters) are skipped as a coverage gap. */
    public const int MAX_DIFF_CHARACTERS = 80_000;

    private const array DISALLOWED_EXTENSIONS = ['lock', 'json'];

    /**
     * Summary
     *  - exclude files with baseline in their file path (irrelevant)
     *  - exclude files with disallowed extensions (irrelevant)
     *  - exclude binary files (irrelevant)
     *  - exclude deleted files (irrelevant)
     *  - exclude files with more than MAX_FILE_LINES lines (coverage gap)
     *  - exclude files whose raw diff is larger than MAX_DIFF_CHARACTERS (coverage gap)
     */
    public function getSkipReason(DiffFile $file): ?AiCodeReviewFileSkipReason
    {
        if (str_contains($file->getPathname(), 'baseline')) {
            return AiCodeReviewFileSkipReason::Irrelevant;
        }
        if (in_array(strtolower((string)$file->getFile()?->getExtension()), self::DISALLOWED_EXTENSIONS, true)) {
            return AiCodeReviewFileSkipReason::Irrelevant;
        }
        if ($file->binary || $file->isDeleted()) {
            return AiCodeReviewFileSkipReason::Irrelevant;
        }
        if (count($file->getLines()) > self::MAX_FILE_LINES) {
            return AiCodeReviewFileSkipReason::TooManyLines;
        }
        if (strlen((string)$file->raw) > self::MAX_DIFF_CHARACTERS) {
            return AiCodeReviewFileSkipReason::TooLarge;
        }

        return null;
    }

    public function __invoke(DiffFile $file): bool
    {
        return $this->getSkipReason($file) === null;
    }
}
