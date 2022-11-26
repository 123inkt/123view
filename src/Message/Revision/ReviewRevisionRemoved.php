<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Revision;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

/**
 * Message to notify consumers a revision was removed from a review.
 */
class ReviewRevisionRemoved implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $revisionId)
    {
    }

    public function getName(): string
    {
        return 'review-revision-removed';
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
        return ['reviewId' => $this->reviewId, 'revisionId' => $this->revisionId];
    }
}
