<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class CommentNotFoundException extends ReviewException
{
    public function __construct(int $commentId)
    {
        parent::__construct('Comment not found: ' . $commentId);
    }
}
