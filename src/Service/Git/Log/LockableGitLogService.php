<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
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
    public function getCommitsSince(Repository $repository, ?Revision $revision = null, ?int $limit = null): array
    {
        return $this->lockManager->start($repository, fn() => $this->logService->getCommitsSince($repository, $revision, $limit));
    }
}
