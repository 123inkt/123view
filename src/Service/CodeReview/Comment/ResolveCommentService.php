<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Exception\Ai\CommentNotInReviewException;
use DR\Review\Repository\Review\CommentRepository;

class ResolveCommentService
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * @throws CommentNotFoundException
     * @throws CommentNotInReviewException
     */
    public function resolve(int $commentId, int $reviewId): string
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }

        if ($comment->getReview()->getId() !== $reviewId) {
            throw new CommentNotInReviewException($commentId, $reviewId);
        }

        if ($comment->getState() === CommentStateType::RESOLVED) {
            return sprintf('Comment %d is already resolved.', $commentId);
        }

        $comment->setState(CommentStateType::RESOLVED);
        $this->commentRepository->save($comment, true);

        return sprintf('Comment %d resolved.', $commentId);
    }
}
