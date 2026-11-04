<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class CodeReviewNotFoundException extends ReviewException
{
    public function __construct(int $reviewId)
    {
        parent::__construct('Code review not found: ' . $reviewId);
    }
}
