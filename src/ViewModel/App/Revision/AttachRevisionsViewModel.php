<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use DR\Review\Entity\Review\CodeReview;

/**
 * @codeCoverageIgnore
 */
class AttachRevisionsViewModel
{
    public function __construct(public readonly CodeReview $review, public readonly RevisionsViewModel $revisionsViewModel)
    {
    }
}
