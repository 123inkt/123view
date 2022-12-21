<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Log;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;

class LockableGitLogService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitLogService $logService)
    {
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getCommitHashes(Repository $repository): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitHashes($repository));
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsFromRange(Repository $repository, string $fromReference, string $toReference): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitsFromRange($repository, $fromReference, $toReference));
    }
}
