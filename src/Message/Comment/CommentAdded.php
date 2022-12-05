<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

class CommentAdded implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public const NAME = 'comment-added';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentId,
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
        return ['commentId' => $this->commentId, 'message' => $this->message];
    }
}
