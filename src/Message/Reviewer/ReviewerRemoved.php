<?php
declare(strict_types=1);

namespace DR\Review\Message\Reviewer;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;

class ReviewerRemoved implements AsyncMessageInterface, CodeReviewAwareInterface, UserAwareInterface
{
    public const NAME = 'reviewer-removed';

    public function __construct(public readonly int $reviewId, public readonly int $userId, public readonly int $byUserId)
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
