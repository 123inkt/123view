<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;

class CommentUpdated implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public const NAME = 'comment-updated';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentId,
        public readonly int $byUserId,
        public readonly string $originalComment
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

    public function getCommentId(): int
    {
        return $this->commentId;
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
        return ['commentId' => $this->commentId, 'originalComment' => $this->originalComment];
    }
}
