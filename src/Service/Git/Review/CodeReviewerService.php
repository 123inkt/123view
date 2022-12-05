<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;

class CodeReviewerService
{
    public function addReviewer(CodeReview $review, User $user): CodeReviewer
    {
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $reviewer->setState(CodeReviewerStateType::OPEN);
        $reviewer->setStateTimestamp(time());
        $reviewer->setReview($review);
        $review->getReviewers()->add($reviewer);

        return $reviewer;
    }

    public function setReviewerState(CodeReview $review, CodeReviewer $reviewer, string $state): void
    {
        $reviewer->setState($state);
        if ($review->isAccepted()) {
            // resolve all comments
            foreach ($review->getComments() as $comment) {
                $comment->setState(CommentStateType::RESOLVED);
            }
            $review->setState(CodeReviewStateType::CLOSED);
        } else {
            $review->setState(CodeReviewStateType::OPEN);
        }
    }
}
