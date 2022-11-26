<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;

/**
 * @codeCoverageIgnore
 */
class AttachRevisionsViewModel
{
    public function __construct(public readonly CodeReview $review, public readonly RevisionsViewModel $revisionsViewModel)
    {
    }
}
