<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;

class ReviewViewModel
{
    public function __construct(private readonly CodeReview $review)
    {
    }

    public function getReview(): CodeReview
    {
        return $this->review;
    }
}
