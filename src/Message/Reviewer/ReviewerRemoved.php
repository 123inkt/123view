<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Reviewer;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;

class ReviewerRemoved implements AsyncMessageInterface, CodeReviewAwareInterface, UserAwareInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $userId, public readonly int $byUserId)
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

    public function getUserId(): int
    {
        return $this->byUserId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId, 'userId' => $this->userId, 'byUserId' => $this->byUserId];
    }
}
