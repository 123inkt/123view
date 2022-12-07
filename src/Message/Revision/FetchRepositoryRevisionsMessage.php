<?php
declare(strict_types=1);

namespace DR\Review\Message\Revision;

/**
 * Message to notify consumers to fetch new revisions from given repository
 * @codeCoverageIgnore
 */
class FetchRepositoryRevisionsMessage
{
    public function __construct(public readonly int $repositoryId)
    {
    }
}
