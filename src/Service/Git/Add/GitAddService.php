<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Add;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;

class GitAddService
{
    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function add(Repository $repository, string $path): void
    {
        $commandBuilder = $this->commandFactory->createAdd()->setPath($path);

        // create branch
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }
}
