<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitCommitService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $commandBuilder = $this->commandFactory->createCommit()->message($message);

        // commit
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
