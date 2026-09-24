<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Model\Git\GitRepository;

class CacheableGitRepositoryService
{
    /** @var array<int, GitRepository> */
    private array $repositories = [];

    public function __construct(private readonly GitRepositoryService $gitRepositoryService)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function getRepository(Repository $repository): GitRepository
    {
        return $this->repositories[$repository->getId()] ??= $this->gitRepositoryService->getRepository($repository);
    }
}
