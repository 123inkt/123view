<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Review;

use DR\GitCommitNotification\Message\AsyncMessageInterface;

class ReviewCreated implements AsyncMessageInterface, CodeReviewEventInterface
{
    public function __construct(public readonly int $reviewId)
    {
    }

    public function getName(): string
    {
        return 'review-created';
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    public function getUserId(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId];
    }
}
