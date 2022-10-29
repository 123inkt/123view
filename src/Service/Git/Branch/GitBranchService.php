<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Branch;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitBranchService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    public function tryDeleteBranch(Repository $repository, string $ref): bool
    {
        try {
            $this->deleteBranch($repository, $ref);

            return true;
        } catch (RepositoryException | ProcessFailedException $exception) {
            $this->logger?->notice('Recovered from exception', ['exception' => $exception]);

            return false;
        }
    }

    /**
     * @throws RepositoryException
     */
    public function deleteBranch(Repository $repository, string $ref): void
    {
        $commandBuilder = $this->commandFactory->createBranch()->delete($ref);

        // delete branch
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
