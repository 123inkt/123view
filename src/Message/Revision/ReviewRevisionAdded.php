<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Revision;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;

/**
 * Message to notify consumers a new revision was added to the a review.
 */
class ReviewRevisionAdded implements AsyncMessageInterface, CodeReviewAwareInterface, UserAwareInterface
{
    public const NAME = 'review-revision-added';

    public function __construct(public readonly int $reviewId, public readonly int $revisionId, public readonly ?int $byUserId)
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
        return ['reviewId' => $this->reviewId, 'revisionId' => $this->revisionId, 'userId' => $this->byUserId];
    }
}
