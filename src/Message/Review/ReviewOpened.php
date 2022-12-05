<?php
declare(strict_types=1);

namespace DR\Review\Message\Review;

use DR\Review\Message\AsyncMessageInterface;

class ReviewOpened implements AsyncMessageInterface, CodeReviewEventInterface
{
    public const NAME = 'review-opened';

    public function __construct(public readonly int $reviewId, public readonly ?int $byUserId)
    {
    }

    public function getName(): string
    {
        return self::NAME;
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
