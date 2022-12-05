<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

use DR\Review\Message\AsyncMessageInterface;

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
