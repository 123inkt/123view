<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

class CommentAdded implements AsyncMessageInterface, WebhookEventInterface, MailNotificationInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $commentId)
    {
    }

    public function getName(): string
    {
        return 'comment-added';
    }

    public function getReviewId(): int
    {
        return $this->reviewId;
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentId];
    }
}
