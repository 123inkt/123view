<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;

interface CommentReplyEventInterface extends CodeReviewAwareInterface
{
    public function getCommentReplyId(): int;
}
