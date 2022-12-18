<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;

class CacheableGitRepositoryService extends GitRepositoryService
{
    /** @var array<string, GitRepository> */
    private array $repositories = [];

    /**
     * @throws RepositoryException
     */
    public function getRepository(string $repositoryUrl): GitRepository
    {
        if (isset($this->repositories[$repositoryUrl]) === false) {
            $this->repositories[$repositoryUrl] = parent::getRepository($repositoryUrl);
        }

        return $this->repositories[$repositoryUrl];
    }
}
