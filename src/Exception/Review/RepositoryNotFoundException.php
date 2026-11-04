<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class RepositoryNotFoundException extends ReviewException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $repositoryName)
    {
        parent::__construct('Repository not found: ' . $repositoryName);
    }
}
