<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Revision;

use DR\GitCommitNotification\Message\AsyncMessageInterface;

/**
 * Message to notify consumers a new revision was added to the database.
 * @codeCoverageIgnore
 */
class NewRevisionMessage implements AsyncMessageInterface
{
    public function __construct(public readonly int $revisionId)
    {
    }
}
