<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

use DR\Review\Message\AsyncMessageInterface;

/**
 * Message to notify consumers to fetch new revisions from given repository
 * @codeCoverageIgnore
 */
class FetchRepositoryRevisionsMessage implements AsyncMessageInterface
{
    public function __construct(public readonly int $repositoryId)
    {
    }
}
