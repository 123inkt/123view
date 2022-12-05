<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;

class CommentReplyUpdated implements AsyncMessageInterface, MailNotificationInterface, CommentReplyEventInterface
{
    public const NAME = 'comment-reply-updated';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentReplyId,
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

    public function getCommentReplyId(): int
    {
        return $this->commentReplyId;
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
        return ['commentId' => $this->commentReplyId, 'originalComment' => $this->originalComment];
    }
}
