<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;

class CommentResolved implements AsyncMessageInterface, MailNotificationInterface, CommentEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $commentId, public readonly int $resolveByUserId)
    {
    }

    public function getName(): string
    {
        return 'comment-resolved';
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentId, 'resolved-by-user-id' => $this->resolveByUserId];
    }
}
