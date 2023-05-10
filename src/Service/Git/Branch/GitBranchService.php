<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitBranchService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
        private readonly GitRemoteBranchParser $branchParser
    ) {
    }

    /**
     * @return non-empty-string[]
     * @throws RepositoryException
     */
    public function getRemoteBranches(Repository $repository): array
    {
        $commandBuilder = $this->commandFactory->createBranch()->remote();

        // list remote branches
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);
        $this->logger?->info($output);

        return $this->branchParser->parse($output);
    }

    public function tryDeleteBranch(Repository $repository, string $ref): bool
    {
        try {
            $this->deleteBranch($repository, $ref);

            return true;
        } catch (RepositoryException|ProcessFailedException $exception) {
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
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
