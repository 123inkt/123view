<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

readonly class CommentReplyUpdated implements AsyncMessageInterface, MailNotificationInterface, CommentReplyEventInterface
{
    public const NAME = 'comment-reply-updated';

    public function __construct(
        public int $reviewId,
        public int $commentReplyId,
        public int $byUserId,
        public string $originalComment,
        public readonly CommentModificationEnum $modifiedBy = CommentModificationEnum::Local,
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

    public function getModifiedBy(): CommentModificationEnum
    {
        return $this->modifiedBy;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['commentId' => $this->commentReplyId, 'originalComment' => $this->originalComment, 'modifiedBy' => $this->modifiedBy->value];
    }
}
