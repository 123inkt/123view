<?php
declare(strict_types=1);

namespace DR\Review\Message\Review;

use DR\Review\Message\AsyncMessageInterface;

class ReviewCreated implements AsyncMessageInterface, CodeReviewEventInterface
{
    public const NAME = 'review-created';

    public function __construct(public readonly int $reviewId, public readonly int $revisionId, public readonly ?int $userId = null)
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
        return $this->userId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId, 'revisionId' => $this->revisionId, 'userId' => $this->userId];
    }
}
