<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\LsTree;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;

class LockableLsTreeService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly LsTreeService $lsTreeService)
    {
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function listFiles(Revision $revision, string $filepath): array
    {
        return $this->lockManager->start($revision->getRepository(), fn() => $this->lsTreeService->listFiles($revision, $filepath));
    }
}
