<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Reviewer;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

class ReviewerRemoved implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $userId)
    {
    }

    public function getName(): string
    {
        return 'reviewer-removed';
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
        return ['reviewId' => $this->reviewId, 'userId' => $this->userId];
    }
}
