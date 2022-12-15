<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Log;

use DateTime;
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
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsSince(Repository $repository, ?DateTime $since = null, ?int $limit = null): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitsSince($repository, $since, $limit));
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsFromRange(Repository $repository, string $fromHash, string $toHash): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitsFromRange($repository, $fromHash, $toHash));
    }
}
