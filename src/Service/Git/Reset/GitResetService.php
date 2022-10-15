<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Reset;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitResetService implements LoggerAwareInterface
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
    public function resetHard(Repository $repository): void
    {
        $commandBuilder = $this->commandFactory->createReset()->hard();

        // merge given hashes
        $output = $this->repositoryService->getRepository($repository->getUrl())->execute($commandBuilder);

        $this->logger->info($output);
    }
}
