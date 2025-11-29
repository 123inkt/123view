<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;

class LockableGitShowService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitShowService $showService)
    {
    }

    /**
     * @throws Exception
     */
    public function getCommitFromHash(Repository $repository, string $commitHash): ?Commit
    {
        return $this->lockManager->start($repository, fn() => $this->showService->getCommitFromHash($repository, $commitHash));
    }

    /**
     * @throws RepositoryException
     */
    public function getFileContents(Revision $revision, string $file, bool $binary = false): string
    {
        return $this->lockManager->start($revision->getRepository(), fn() => $this->showService->getFileContents($revision, $file, $binary));
    }
}
