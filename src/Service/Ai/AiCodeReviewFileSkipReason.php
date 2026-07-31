<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

/**
 * Classifies why a changed file was excluded from an AI code review.
 */
enum AiCodeReviewFileSkipReason
{
    /** Deliberately excluded (baseline path, disallowed extension, binary, or deleted). Not a coverage gap. */
    case Irrelevant;

    /** Excluded because the file has more changed lines than the review can safely process. */
    case TooManyLines;

    /** Excluded because the raw diff is larger than the configured per-file character budget. */
    case TooLarge;

    /**
     * Whether this reason represents a real gap in review coverage that should be
     * surfaced to reviewers, as opposed to a deliberate, silent exclusion.
     */
    public function isCoverageGap(): bool
    {
        return $this === self::TooManyLines || $this === self::TooLarge;
    }
}
