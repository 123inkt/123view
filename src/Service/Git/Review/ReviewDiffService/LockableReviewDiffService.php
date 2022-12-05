<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Review\FileDiffOptions;

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
