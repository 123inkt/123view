<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Clean;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;

class GitCleanService
{
    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function forceClean(Repository $repository): void
    {
        $commandBuilder = $this->commandFactory->createClean()->recurseDirectories()->skipIgnoreRules()->force();

        // execute command
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }
}
