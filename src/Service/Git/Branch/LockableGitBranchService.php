<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Branch;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Psr\Log\LoggerAwareTrait;

class LockableGitBranchService
{
    use LoggerAwareTrait;

    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitBranchService $branchService)
    {
    }

    /**
     * @return string[]
     * @throws RepositoryException
     */
    public function getRemoteBranches(Repository $repository, bool $mergedOnly = false): array
    {
        return $this->lockManager->start($repository, fn() => $this->branchService->getRemoteBranches($repository, $mergedOnly));
    }

    public function tryDeleteBranch(Repository $repository, string $ref): bool
    {
        return $this->lockManager->start($repository, fn() => $this->branchService->tryDeleteBranch($repository, $ref));
    }

    /**
     * @throws RepositoryException
     */
    public function deleteBranch(Repository $repository, string $ref): void
    {
        $this->lockManager->start($repository, fn() => $this->branchService->deleteBranch($repository, $ref));
    }
}
