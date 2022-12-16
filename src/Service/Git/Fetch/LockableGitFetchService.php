<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;

class LockableGitFetchService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitFetchService $fetchService)
    {
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function fetch(Repository $repository): array
    {
        return $this->lockManager->start($repository, fn() => $this->fetchService->fetch($repository));
    }
}
