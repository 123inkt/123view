<?php

declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;

class CommentVisibility
{
    /**
     * Only show draft comment to comment owner
     */
    public function isVisible(Comment $comment, User $user): bool
    {
        return $comment->getType() === CommentTypeEnum::Final || $comment->getUser()->getId() === $user->getId();
    }
}
