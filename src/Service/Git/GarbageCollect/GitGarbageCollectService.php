<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\GarbageCollect;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitGarbageCollectService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function garbageCollect(Repository $repository, string $date): void
    {
        $commandBuilder = $this->commandFactory
            ->createGarbageCollect()
            ->prune($date)
            ->quiet();

        // execute command
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
