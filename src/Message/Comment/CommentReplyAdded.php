<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;

class CommentReplyAdded implements AsyncMessageInterface, MailNotificationInterface, CommentReplyEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $commentReplyId)
    {
    }

    public function getName(): string
    {
        return 'comment-reply-added';
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    public function getCommentReplyId(): int
    {
        return $this->commentReplyId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentReplyId];
    }
}
