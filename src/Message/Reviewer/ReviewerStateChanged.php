<?php
declare(strict_types=1);

namespace DR\Review\Message\Reviewer;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;

class ReviewerStateChanged implements AsyncMessageInterface, CodeReviewAwareInterface, UserAwareInterface
{
    public const NAME = 'reviewer-state-changed';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $reviewerId,
        public readonly int $userId,
        public readonly string $oldState,
        public readonly string $newState
    ) {
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
        return $this->userId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return [
            'reviewId'   => $this->reviewId,
            'reviewerId' => $this->reviewerId,
            'userId'     => $this->userId,
            'oldState'   => $this->oldState,
            'newState'   => $this->newState,
        ];
    }
}
