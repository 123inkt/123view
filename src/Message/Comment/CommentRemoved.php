<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Message\AsyncMessageInterface;

class CommentRemoved implements AsyncMessageInterface, CommentEventInterface
{
    public const NAME = 'comment-removed';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentId,
        public readonly int $byUserId,
        public readonly string $file,
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
        return ['commentId' => $this->commentId, 'file' => $this->file, 'message' => $this->message];
    }
}
