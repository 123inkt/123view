<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\GarbageCollect;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;

class LockableGitGarbageCollectService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitGarbageCollectService $service)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function garbageCollect(Repository $repository, string $date): void
    {
        $this->lockManager->start($repository, fn() => $this->service->garbageCollect($repository, $date));
    }
}
