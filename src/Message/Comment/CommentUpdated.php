<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

readonly class CommentUpdated implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public const NAME = 'comment-updated';

    public function __construct(
        public int $reviewId,
        public int $commentId,
        public int $byUserId,
        public string $file,
        public string $message,
        public CommentModificationEnum $modifiedBy,
        public string $originalComment,
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

    public function getModifiedBy(): CommentModificationEnum
    {
        return $this->modifiedBy;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return [
            'commentId'       => $this->commentId,
            'file'            => $this->file,
            'message'         => $this->message,
            'originalComment' => $this->originalComment,
            'modifiedBy'      => $this->modifiedBy->value,
        ];
    }
}
