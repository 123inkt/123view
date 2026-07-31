<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Review\AiReviewCoverageIncomplete;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Publishes a review-level activity summarizing which changed files an AI code review was unable
 * to fully cover, so incomplete coverage is always visible to reviewers rather than silently assumed
 * to be complete.
 */
class AiCodeReviewCoverageReporter
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    /**
     * @param string[] $skippedFiles files skipped for exceeding size limits
     * @param string[] $failedFiles  files that failed during review
     */
    public function reportIncompleteCoverage(CodeReview $review, array $skippedFiles, array $failedFiles, int $reviewedFileCount): void
    {
        if (count($skippedFiles) === 0 && count($failedFiles) === 0) {
            return;
        }

        $this->messageBus->dispatch(new AiReviewCoverageIncomplete($review->getId(), $skippedFiles, $failedFiles, $reviewedFileCount));
    }
}
