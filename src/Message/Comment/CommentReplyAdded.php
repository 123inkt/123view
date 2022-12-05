<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

class CommentReplyAdded implements AsyncMessageInterface, MailNotificationInterface, CommentReplyEventInterface
{
    public const NAME = 'comment-reply-added';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentReplyId,
        public readonly int $byUserId,
        public readonly string $message
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
        return ['commentId' => $this->commentReplyId, 'message' => $this->message];
    }
}
