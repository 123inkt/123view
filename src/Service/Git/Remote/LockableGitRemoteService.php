<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;

class LockableGitRemoteService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitRemoteService $remoteService)
    {
    }

    /**
     * @throws RepositoryException
     */
    public function updateRemoteUrl(Repository $repository): void
    {
        $this->lockManager->start($repository, fn() => $this->remoteService->updateRemoteUrl($repository));
    }
}
