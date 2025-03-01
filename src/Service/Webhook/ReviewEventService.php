<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use Symfony\Component\Messenger\MessageBusInterface;

class ReviewEventService
{
    public function __construct(private readonly CodeReviewerStateResolver $reviewerStateResolver, private readonly MessageBusInterface $bus)
    {
    }

    public function reviewerAdded(CodeReview $review, CodeReviewer $reviewer, int $byUserId, bool $added): void
    {
        if ($added) {
            $this->bus->dispatch(new ReviewerAdded((int)$review->getId(), $reviewer->getUser()->getId(), $byUserId));
        }
    }

    public function reviewerRemoved(CodeReview $review, CodeReviewer $reviewer, int $byUserId): void
    {
        $this->bus->dispatch(new ReviewerRemoved((int)$review->getId(), $reviewer->getUser()->getId(), $byUserId));
    }

    public function reviewerStateChanged(CodeReview $review, CodeReviewer $reviewer, string $previousState): void
    {
        if ($reviewer->getState() === $previousState) {
            return;
        }

        $event = new ReviewerStateChanged(
            (int)$review->getId(),
            (int)$reviewer->getId(),
            $reviewer->getUser()->getId(),
            $previousState,
            $reviewer->getState()
        );
        $this->bus->dispatch($event);
    }

    public function reviewReviewerStateChanged(CodeReview $review, string $previousReviewerState, int $byUserId): void
    {
        $reviewerState = $this->reviewerStateResolver->getReviewersState($review);
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

    public function reviewStateChanged(CodeReview $review, string $reviewState, ?int $byUserId): void
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
    public function revisionsAdded(CodeReview $review, array $revisions, ?int $byUserId): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId(), $byUserId, $revision->getTitle()));
        }
    }

    /**
     * @param Revision[] $detachedRevisions
     */
    public function revisionsDetached(CodeReview $review, array $detachedRevisions, ?int $byUserId): void
    {
        foreach ($detachedRevisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionRemoved((int)$review->getId(), (int)$revision->getId(), $byUserId, $revision->getTitle()));
        }
    }
}
