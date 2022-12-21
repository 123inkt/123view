<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

class CommitRemovedMessage
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly int $repositoryId, public string $commitHash)
    {
    }
}
