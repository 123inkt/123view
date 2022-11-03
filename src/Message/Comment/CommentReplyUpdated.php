<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\MailNotificationInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

class CommentReplyUpdated implements AsyncMessageInterface, WebhookEventInterface, MailNotificationInterface
{
    public function __construct(public readonly int $commentReplyId, public readonly string $originalComment)
    {
    }

    public function getName(): string
    {
        return 'comment-reply-updated';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentReplyId, 'original-comment' => $this->originalComment];
    }
}
