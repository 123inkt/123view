<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;

class CodeReviewTypeDecider
{
    /**
     * @param Revision[] $revisions
     * @param Revision[] $visibleRevisions
     *
     * @return CodeReviewType::COMMITS|CodeReviewType::BRANCH
     */
    public function decide(CodeReview $review, array $revisions, array $visibleRevisions): string
    {
        if ($review->getType() === CodeReviewType::BRANCH && count($revisions) === count($visibleRevisions)) {
            return CodeReviewType::BRANCH;
        }

        return CodeReviewType::COMMITS;
    }
}
