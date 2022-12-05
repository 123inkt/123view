<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

class CommentResolved implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public const NAME = 'comment-resolved';

    public function __construct(public readonly int $reviewId, public readonly int $commentId, public readonly int $resolveByUserId)
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

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function getUserId(): int
    {
        return $this->resolveByUserId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['commentId' => $this->commentId, 'resolvedByUserId' => $this->resolveByUserId];
    }
}
