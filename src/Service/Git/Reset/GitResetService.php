<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Reset;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
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
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
