<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Review;

use DR\GitCommitNotification\Message\AsyncMessageInterface;

class ReviewOpened implements AsyncMessageInterface, CodeReviewEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly ?int $byUserId)
    {
    }

    public function getName(): string
    {
        return 'review-opened';
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    public function getUserId(): ?int
    {
        return $this->byUserId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId, 'userId' => $this->byUserId];
    }
}
