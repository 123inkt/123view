<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;

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
