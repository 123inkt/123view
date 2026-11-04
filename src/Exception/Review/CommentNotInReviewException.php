<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class CommentNotInReviewException extends ReviewException
{
    public function __construct(int $commentId, int $reviewId)
    {
        parent::__construct(sprintf('Comment %d does not belong to review %d.', $commentId, $reviewId));
    }
}
