<?php
declare(strict_types=1);

namespace DR\Review\Message\Review;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\CodeReviewAwareInterface;

/**
 * Dispatched when an AI code review completed but did not cover every changed file, either
 * because some files were skipped for being too large, or because reviewing them failed.
 */
class AiReviewCoverageIncomplete implements AsyncMessageInterface, CodeReviewAwareInterface
{
    public const NAME = 'ai-review-coverage-incomplete';

    /**
     * @param string[] $skippedFiles files skipped for exceeding size limits
     * @param string[] $failedFiles  files that threw during review
     */
    public function __construct(
        public readonly int $reviewId,
        public readonly array $skippedFiles,
        public readonly array $failedFiles,
        public readonly int $reviewedFileCount,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return [
            'reviewedCount' => $this->reviewedFileCount,
            'skippedCount'  => count($this->skippedFiles),
            'failedCount'   => count($this->failedFiles),
            'skippedFiles'  => implode(', ', $this->skippedFiles),
            'failedFiles'   => implode(', ', $this->failedFiles),
        ];
    }
}
