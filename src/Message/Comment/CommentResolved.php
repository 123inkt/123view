<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

class CommentResolved implements AsyncMessageInterface, WebhookEventInterface, MailNotificationInterface
{
    public function __construct(public readonly int $commentId, public readonly int $resolveByUserId)
    {
    }

    public function getName(): string
    {
        return 'comment-resolved';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentId, 'resolved-by-user-id' => $this->resolveByUserId];
    }
}
