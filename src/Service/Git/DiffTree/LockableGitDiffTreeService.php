<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\DiffTree;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Utility\Assert;
use Exception;

class LockableGitDiffTreeService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitDiffTreeService $treeService)
    {
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getFilesInRevision(Revision $revision): array
    {
        return $this->lockManager->start(Assert::notNull($revision->getRepository()), fn() => $this->treeService->getFilesInRevision($revision));
    }
}
