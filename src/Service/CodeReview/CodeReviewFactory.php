<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Revision\RevisionTitleNormalizer;

class CodeReviewFactory
{
    public function __construct(private RevisionTitleNormalizer $titleNormalizer)
    {
    }

    public function createFromRevision(Revision $revision, string $referenceId): CodeReview
    {
        $review = new CodeReview();
        $review->setCreateTimestamp(time());
        $review->setReferenceId($referenceId);
        $review->setTitle($this->titleNormalizer->normalize((string)$revision->getTitle()));
        $review->setDescription($revision->getDescription());
        $review->setRepository($revision->getRepository());

        return $review;
    }
}
