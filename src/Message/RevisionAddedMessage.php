<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

/**
 * Message to notify consumers a new revision was added to the database.
 * @codeCoverageIgnore
 */
class RevisionAddedMessage implements AsyncMessageInterface, NotifyWebhookInterface
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
        return ['revision-id' => $this->revisionId];
    }
}
