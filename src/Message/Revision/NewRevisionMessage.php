<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Revision;

use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\WebhookEventInterface;

/**
 * Message to notify consumers a new revision was added to the database.
 */
class NewRevisionMessage implements AsyncMessageInterface, WebhookEventInterface
{
    public function __construct(public readonly int $revisionId)
    {
    }

    public function getName(): string
    {
        return 'revision-added';
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): array
    {
        return ['revisionId' => $this->revisionId];
    }
}
