<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

class RepositoryUpdatedMessage
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly int $repositoryId)
    {
    }
}
