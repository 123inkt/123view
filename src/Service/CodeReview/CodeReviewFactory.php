<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer;

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
        $review->setRepository($revision->getRepository());

        return $review;
    }
}
