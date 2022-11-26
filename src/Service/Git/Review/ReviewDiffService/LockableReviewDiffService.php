<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;

class LockableReviewDiffService implements ReviewDiffServiceInterface
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly ReviewDiffServiceInterface $diffService)
    {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        return $this->lockManager->start($repository, fn() => $this->diffService->getDiffFiles($repository, $revisions, $options));
    }
}
