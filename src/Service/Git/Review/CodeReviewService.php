<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\CodeReviewerRepository;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;

class CodeReviewService
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CodeReviewerRepository $reviewerRepository
    ) {
    }

    /**
     * @param Revision[] $revisions
     */
    public function addRevisions(CodeReview $review, array $revisions): void
    {
        foreach ($revisions as $revision) {
            $revision->setReview($review);
            $review->getRevisions()->add($revision);
            $this->revisionRepository->save($revision, true);
        }

        $review->setState(CodeReviewStateType::OPEN);
        $this->reviewRepository->save($review, true);

        foreach ($review->getReviewers() as $reviewer) {
            $reviewer->setState(CodeReviewerStateType::OPEN);
            $this->reviewerRepository->save($reviewer, true);
        }
    }
}
