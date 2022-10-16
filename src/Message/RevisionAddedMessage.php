<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

/**
 * Message to notify consumers a new revision was added to the database.
 */
class RevisionAddedMessage implements AsyncMessageInterface
{
    public function __construct(public readonly int $revisionId)
    {
    }
}
