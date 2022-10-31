<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Revision;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

/**
 * Message to notify consumers a new revision was added to the a review.
 */
class ReviewRevisionAdded implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $reviewId, public readonly int $revisionId)
    {
    }

    public function getName(): string
    {
        return 'review-revision-added';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['reviewId' => $this->reviewId, 'revisionId' => $this->revisionId];
    }
}
