<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

class RevisionAddedMessage implements AsyncMessageInterface
{
    public function __construct(public readonly int $revisionId)
    {
    }
}
