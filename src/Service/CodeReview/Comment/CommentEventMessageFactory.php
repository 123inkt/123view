<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Message\Comment\CommentUpdated;

class CommentEventMessageFactory
{
    public function createAdded(Comment $comment, User $user): CommentAdded
    {
        return new CommentAdded(
            $comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getFilePath(),
            $comment->getMessage()
        );
    }

    public function createUpdated(Comment $comment, User $user, string $originalComment): CommentUpdated
    {
        return new CommentUpdated(
            $comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getFilePath(),
            $comment->getMessage(),
            $originalComment
        );
    }

    public function createResolved(Comment $comment, User $user): CommentResolved
    {
        return new CommentResolved(
            $comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getFilePath(),
        );
    }

    public function createUnresolved(Comment $comment, User $user): CommentUnresolved
    {
        return new CommentUnresolved(
            $comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getFilePath(),
        );
    }

    public function createRemoved(Comment $comment, User $user): CommentRemoved
    {
        return new CommentRemoved(
            $comment->getReview()->getId(),
            (int)$comment->getId(),
            $user->getId(),
            $comment->getFilePath(),
            $comment->getMessage(),
            $comment->getExtReferenceId()
        );
    }

    public function createReplyRemoved(CommentReply $reply, User $user): CommentReplyRemoved
    {
        return new CommentReplyRemoved(
            $reply->getComment()->getReview()->getId(),
            (int)$reply->getComment()->getId(),
            (int)$reply->getId(),
            $reply->getUser()->getId(),
            $user->getId(),
            $reply->getMessage(),
            $reply->getExtReferenceId()
        );
    }
}
