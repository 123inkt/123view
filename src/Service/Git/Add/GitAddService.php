<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Add;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitAddService implements LoggerAwareInterface
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
    public function add(Repository $repository, string $path): void
    {
        $commandBuilder = $this->commandFactory->createAdd()->setPath($path);

        $this->logger?->info('Executing: ' . $commandBuilder);

        // create branch
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
