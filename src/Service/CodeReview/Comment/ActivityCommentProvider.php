<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;

class ActivityCommentProvider
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
    ) {
    }

    public function getCommentFor(CodeReviewActivity $activity): Comment|CommentReply|null
    {
        if ($activity->getEventName() === CommentAdded::NAME) {
            return $this->commentRepository->find((int)$activity->getDataValue('commentId'));
        }

        if ($activity->getEventName() === CommentReplyAdded::NAME) {
            return $this->replyRepository->find((int)$activity->getDataValue('commentId'));
        }

        return null;
    }
}
