<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

class ReviewerAdded implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $userId)
    {
    }

    public function getName(): string
    {
        return 'reviewer-added';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId, 'userId' => $this->userId];
    }
}
