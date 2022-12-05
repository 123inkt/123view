<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;

interface CommentReplyEventInterface extends CodeReviewAwareInterface, UserAwareInterface
{
    public function getCommentReplyId(): int;
}
