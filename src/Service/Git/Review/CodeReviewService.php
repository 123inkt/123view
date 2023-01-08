<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewerRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;

class CodeReviewService
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CodeReviewerRepository $reviewerRepository,
        private readonly RevisionVisibilityService $visibilityService,
    ) {
    }

    /**
     * @param Revision[] $revisions
     */
    public function addRevisions(CodeReview $review, array $revisions): void
    {
        $previousRevisions = $review->getRevisions()->toArray();

        foreach ($revisions as $revision) {
            $revision->setReview($review);
            $review->getRevisions()->add($revision);
            $this->revisionRepository->save($revision, true);
        }

        $review->setState(CodeReviewStateType::OPEN);
        $this->reviewRepository->save($review, true);

        foreach ($review->getReviewers() as $reviewer) {
            if ($reviewer->getState() === CodeReviewerStateType::OPEN) {
                continue;
            }
            $reviewer->setState(CodeReviewerStateType::OPEN);
            $this->reviewerRepository->save($reviewer, true);
            $this->visibilityService->setRevisionVisibility($review, $previousRevisions, $reviewer->getUser(), false);
        }
    }
}
