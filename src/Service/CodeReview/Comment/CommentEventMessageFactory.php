<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Message\Comment\CommentUpdated;

class CommentEventMessageFactory
{
    public function createAdded(Comment $comment, User $user): CommentAdded
    {
        return new CommentAdded(
            (int)$comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getLineReference()->filePath,
            $comment->getMessage()
        );
    }

    public function createUpdated(Comment $comment, User $user, string $originalComment): CommentUpdated
    {
        return new CommentUpdated(
            (int)$comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getLineReference()->filePath,
            $comment->getMessage(),
            $originalComment
        );
    }

    public function createResolved(Comment $comment, User $user): CommentResolved
    {
        return new CommentResolved(
            (int)$comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getLineReference()->filePath,
        );
    }

    public function createUnresolved(Comment $comment, User $user): CommentUnresolved
    {
        return new CommentUnresolved(
            (int)$comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getLineReference()->filePath,
        );
    }

    public function createRemoved(Comment $comment, User $user): CommentRemoved
    {
        return new CommentRemoved(
            (int)$comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getLineReference()->filePath,
            $comment->getMessage(),
        );
    }
}
