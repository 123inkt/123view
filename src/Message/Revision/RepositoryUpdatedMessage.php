<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

use DR\Review\Message\AsyncMessageInterface;

class RepositoryUpdatedMessage implements AsyncMessageInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly int $repositoryId)
    {
    }
}
