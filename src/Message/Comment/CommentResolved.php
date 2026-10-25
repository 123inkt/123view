<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\AsyncMessageInterface;
use DR\Review\Message\MailNotificationInterface;

class CommentResolved implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public const NAME = 'comment-resolved';

    public function __construct(
        public readonly int $reviewId,
        public readonly int $commentId,
        public readonly int $resolveByUserId,
        public readonly string $file,
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

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function getUserId(): int
    {
        return $this->resolveByUserId;
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
            'commentId'        => $this->commentId,
            'file'             => $this->file,
            'resolvedByUserId' => $this->resolveByUserId,
            'modifiedBy'       => $this->modifiedBy->value,
        ];
    }
}
