<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Message\ReviewAccepted;
use DR\GitCommitNotification\Message\ReviewClosed;
use DR\GitCommitNotification\Message\ReviewerAdded;
use DR\GitCommitNotification\Message\ReviewOpened;
use DR\GitCommitNotification\Message\ReviewRejected;
use DR\GitCommitNotification\Message\ReviewResumed;
use Symfony\Component\Messenger\MessageBusInterface;

class ReviewEventService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function reviewerAdded(CodeReview $review, CodeReviewer $reviewer, bool $added): void
    {
        if ($added) {
            $this->bus->dispatch(new ReviewerAdded((int)$review->getId(), (int)$reviewer->getUser()?->getId()));
        }
    }

    public function reviewerStateChanged(CodeReview $review, string $previousReviewerState): void
    {
        $reviewerState = $review->getReviewersState();
        if ($reviewerState === $previousReviewerState) {
            return;
        }

        if ($reviewerState === CodeReviewerStateType::REJECTED) {
            $this->bus->dispatch(new ReviewRejected((int)$review->getId()));
        } elseif ($reviewerState === CodeReviewerStateType::ACCEPTED) {
            $this->bus->dispatch(new ReviewAccepted((int)$review->getId()));
        } elseif ($reviewerState === CodeReviewerStateType::OPEN) {
            $this->bus->dispatch(new ReviewResumed((int)$review->getId()));
        }
    }

    public function reviewStateChanged(CodeReview $review, string $reviewState): void
    {
        if ($review->getState() === $reviewState) {
            return;
        }
        if ($review->getState() === CodeReviewStateType::OPEN) {
            $this->bus->dispatch(new ReviewOpened((int)$review->getId()));
        } elseif ($review->getState() === CodeReviewStateType::CLOSED) {
            $this->bus->dispatch(new ReviewClosed((int)$review->getId()));
        }
    }
}
