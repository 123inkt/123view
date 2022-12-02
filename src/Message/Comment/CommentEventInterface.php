<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;

interface CommentEventInterface extends CodeReviewAwareInterface
{
    public function getCommentId(): int;
}
