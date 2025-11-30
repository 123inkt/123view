<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;

class GitCommitService
{
    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function commit(Repository $repository, string $message = "."): void
    {
        $commandBuilder = $this->commandFactory->createCommit()->allowEmpty()->message($message);

        // commit
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }
}
