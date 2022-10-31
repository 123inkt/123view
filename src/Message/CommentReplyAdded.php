<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

class CommentReplyAdded implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $commentReplyId)
    {
    }

    public function getName(): string
    {
        return 'comment-reply-added';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentReplyId];
    }
}
