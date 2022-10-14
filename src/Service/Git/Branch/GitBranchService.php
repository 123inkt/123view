<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Branch;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitBranchService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitBranchCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function deleteBranch(Repository $repository, string $ref): void
    {
        $commandBuilder = $this->commandFactory->create()->delete($ref);

        // delete branch
        $output = $this->repositoryService->getRepository($repository->getUrl())->execute($commandBuilder);

        $this->logger->info($output);
    }
}
