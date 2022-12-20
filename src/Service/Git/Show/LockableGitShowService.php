<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class LockableGitShowService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
}
