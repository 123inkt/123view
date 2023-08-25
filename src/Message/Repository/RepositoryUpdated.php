<?php
declare(strict_types=1);

namespace DR\Review\Message\Repository;

use DR\Review\Message\AsyncMessageInterface;

class RepositoryUpdated implements AsyncMessageInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly int $repositoryId)
    {
    }
}