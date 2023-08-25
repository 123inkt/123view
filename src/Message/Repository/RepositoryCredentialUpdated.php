<?php
declare(strict_types=1);

namespace DR\Review\Message\Repository;

use DR\Review\Message\AsyncMessageInterface;

class RepositoryCredentialUpdated implements AsyncMessageInterface
{
    public function __construct(public readonly int $repositoryCredentialId)
    {
    }
}
