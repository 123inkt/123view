<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Comment;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;

interface CommentEventInterface extends CodeReviewAwareInterface, UserAwareInterface
{
    public function getCommentId(): int;
}
