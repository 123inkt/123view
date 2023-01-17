<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Git\GitRepository;
use DR\Review\Utility\Assert;

class CacheableGitRepositoryService extends GitRepositoryService
{
    /** @var array<int, GitRepository> */
    private array $repositories = [];

    /**
     * @throws RepositoryException
     */
    public function getRepository(Repository $repository): GitRepository
    {
        $repositoryId = Assert::notNull($repository->getId());

        if (isset($this->repositories[$repositoryId]) === false) {
            $this->repositories[$repositoryId] = parent::getRepository($repository);
        }

        return $this->repositories[$repositoryId];
    }
}
