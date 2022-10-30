<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\Mail;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;

class NewCommentViewModel
{
    public function __construct(public readonly CodeReview $review, public readonly Comment $comment)
    {
    }
}
