<?php
declare(strict_types=1);

namespace DR\Review\Message\Review;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;

class AiReviewRequested implements UserAwareInterface, CodeReviewAwareInterface
{
    public const NAME = 'request-ai-review';

    public function __construct(public readonly int $reviewId, public readonly ?int $userId = null)
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
        return ['reviewId' => $this->reviewId, 'userId' => $this->userId];
    }
}
