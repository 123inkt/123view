<?php
declare(strict_types=1);

namespace DR\Review\Message\Comment;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;

interface CommentEventInterface extends CodeReviewAwareInterface, UserAwareInterface
{
    public function getCommentId(): int;

    public function getFile(): string;
}
