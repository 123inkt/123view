<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
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

    public function reviewerRemoved(CodeReview $review, CodeReviewer $reviewer): void
    {
        $this->bus->dispatch(new ReviewerRemoved((int)$review->getId(), (int)$reviewer->getUser()?->getId()));
    }

    public function reviewerStateChanged(CodeReview $review, string $previousReviewerState, int $byUserId): void
    {
        $reviewerState = $review->getReviewersState();
        if ($reviewerState === $previousReviewerState) {
            return;
        }

        if ($reviewerState === CodeReviewerStateType::REJECTED) {
            $this->bus->dispatch(new ReviewRejected((int)$review->getId(), $byUserId));
        } elseif ($reviewerState === CodeReviewerStateType::ACCEPTED) {
            $this->bus->dispatch(new ReviewAccepted((int)$review->getId(), $byUserId));
        } elseif ($reviewerState === CodeReviewerStateType::OPEN) {
            $this->bus->dispatch(new ReviewResumed((int)$review->getId(), $byUserId));
        }
    }

    public function reviewStateChanged(CodeReview $review, string $reviewState, int $byUserId): void
    {
        if ($review->getState() === $reviewState) {
            return;
        }
        if ($review->getState() === CodeReviewStateType::OPEN) {
            $this->bus->dispatch(new ReviewOpened((int)$review->getId(), $byUserId));
        } elseif ($review->getState() === CodeReviewStateType::CLOSED) {
            $this->bus->dispatch(new ReviewClosed((int)$review->getId(), $byUserId));
        }
    }

    /**
     * @param Revision[] $revisions
     */
    public function revisionsAdded(CodeReview $review, array $revisions): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId()));
        }
    }

    /**
     * @param Revision[] $detachedRevisions
     */
    public function revisionsDetached(CodeReview $review, array $detachedRevisions): void
    {
        foreach ($detachedRevisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionRemoved((int)$review->getId(), (int)$revision->getId()));
        }
    }
}
