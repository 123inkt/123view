<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

class CommentAdded implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $commentId)
    {
    }

    public function getName(): string
    {
        return 'comment-added';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['comment-id' => $this->commentId];
    }
}
