<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\ReviewDiffService;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
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
    public function getDiffForRevisions(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        return $this->lockManager->start($repository, fn() => $this->diffService->getDiffForRevisions($repository, $revisions, $options));
    }

    /**
     * @inheritDoc
     */
    public function getDiffForBranch(CodeReview $review, array $revisions, string $branchName, ?FileDiffOptions $options = null): array
    {
        return $this->lockManager->start(
            $review->getRepository(),
            fn() => $this->diffService->getDiffForBranch($review, $revisions, $branchName, $options)
        );
    }
}
