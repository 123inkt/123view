<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;

class CodeReviewService
{
    public function __construct(private readonly RevisionRepository $revisionRepository)
    {
    }

    /**
     * @param Revision[] $revisions
     */
    public function addRevisions(CodeReview $review, array $revisions, bool $persist): void
    {
        foreach ($revisions as $revision) {
            $revision->setReview($review);
            $review->getRevisions()->add($revision);
            if ($persist) {
                $this->revisionRepository->save($revision);
            }
        }
    }
}
