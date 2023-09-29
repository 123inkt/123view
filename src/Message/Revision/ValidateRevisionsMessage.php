<?php

declare(strict_types=1);

namespace DR\Review\Message\Revision;

/**
 * Message to notify the revisions should be revalidated for the given repository
 * @codeCoverageIgnore
 */
class ValidateRevisionsMessage
{
    public function __construct(public readonly int $repositoryId)
    {
    }
}
