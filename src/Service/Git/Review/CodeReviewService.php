<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\RevisionRepository;

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
