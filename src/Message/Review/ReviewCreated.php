<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Review;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

class ReviewCreated implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $reviewId)
    {
    }

    public function getName(): string
    {
        return 'review-created';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId];
    }
}
