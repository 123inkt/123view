<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;

class LockableGitRevListService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitRevListService $logService)
    {
    }

    /**
     * @return string[]
     * @throws RepositoryException
     */
    public function getCommitsAheadOf(Repository $repository, string $branchName, ?string $targetBranch = null): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitsAheadOf($repository, $branchName, $targetBranch));
    }
}
