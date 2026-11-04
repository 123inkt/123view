<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class CommentReplyNotFoundException extends ReviewException
{
    public function __construct(int $commentId)
    {
        parent::__construct('Comment reply not found: ' . $commentId);
    }
}
