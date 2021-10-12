<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Git\GitRepository;

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
