<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Revision\RevisionTitleNormalizer;

class CodeReviewFactory
{
    public function __construct(private RevisionTitleNormalizer $titleNormalizer)
    {
    }

    public function createFromRevision(Revision $revision, ?string $referenceId): CodeReview
    {
        $review = new CodeReview();
        $review->setCreateTimestamp(time());
        $review->setUpdateTimestamp(time());
        $review->setType(CodeReviewType::COMMITS);
        $review->setReferenceId($referenceId);
        $review->setTitle($this->titleNormalizer->normalize($revision->getTitle()));
        $review->setDescription($revision->getDescription());
        $review->setRepository($revision->getRepository());

        return $review;
    }

    public function createFromBranch(Repository $repository, string $branchName): CodeReview
    {
        $review = new CodeReview();
        $review->setCreateTimestamp(time());
        $review->setUpdateTimestamp(time());
        $review->setType(CodeReviewType::BRANCH);
        $review->setReferenceId($branchName);
        $review->setTitle(str_replace(['origin/', '_'], ['', ' '], $branchName));
        $review->setDescription('');
        $review->setRepository($repository);

        return $review;
    }
}
