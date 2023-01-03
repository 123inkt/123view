<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Revision;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class ReviewEventService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function reviewerAdded(CodeReview $review, CodeReviewer $reviewer, int $byUserId, bool $added): void
    {
        if ($added) {
            $this->bus->dispatch(new ReviewerAdded((int)$review->getId(), (int)$reviewer->getUser()?->getId(), $byUserId));
        }
    }

    public function reviewerRemoved(CodeReview $review, CodeReviewer $reviewer, int $byUserId): void
    {
        $this->bus->dispatch(new ReviewerRemoved((int)$review->getId(), (int)$reviewer->getUser()?->getId(), $byUserId));
    }

    public function reviewerStateChanged(CodeReview $review, CodeReviewer $reviewer, string $previousState): void
    {
        if ($reviewer->getState() === $previousState) {
            return;
        }

        $event = new ReviewerStateChanged(
            (int)$review->getId(),
            (int)$reviewer->getId(),
            (int)$reviewer->getUser()?->getId(),
            $previousState,
            (string)$reviewer->getState()
        );
        $this->bus->dispatch($event);
    }

    public function reviewReviewerStateChanged(CodeReview $review, string $previousReviewerState, int $byUserId): void
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
    public function revisionsAdded(CodeReview $review, array $revisions, ?int $byUserId): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId(), $byUserId, (string)$revision->getTitle()));
        }
    }

    /**
     * @param Revision[] $detachedRevisions
     */
    public function revisionsDetached(CodeReview $review, array $detachedRevisions, ?int $byUserId): void
    {
        foreach ($detachedRevisions as $revision) {
            $this->bus->dispatch(new ReviewRevisionRemoved((int)$review->getId(), (int)$revision->getId(), $byUserId, (string)$revision->getTitle()));
        }
    }

    public function revisionAddedToReview(
        CodeReview $review,
        Revision $revision,
        bool $reviewCreated,
        ?string $reviewState,
        string $reviewersState
    ): void {
        $events = [];

        // create events
        if ($reviewCreated) {
            $events[] = new ReviewCreated((int)$review->getId(), (int)$revision->getId());
        }
        if ($reviewState === CodeReviewStateType::CLOSED && $review->getState() === CodeReviewStateType::OPEN) {
            $events[] = new ReviewOpened((int)$review->getId(), null);
        }
        if ($reviewersState !== CodeReviewerStateType::OPEN && $review->getReviewersState() === CodeReviewerStateType::OPEN) {
            $events[] = new ReviewResumed((int)$review->getId(), null);
        }
        $events[] = new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId(), null, (string)$revision->getTitle());

        // dispatch $events
        foreach ($events as $event) {
            $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
        }
    }

    public function revisionRemovedFromReview(CodeReview $review, Revision $revision, ?string $reviewState): void
    {
        $events   = [];
        $events[] = new ReviewRevisionRemoved((int)$review->getId(), (int)$revision->getId(), null, (string)$revision->getTitle());

        // close review event
        if ($reviewState !== $review->getState()) {
            $events[] = new ReviewClosed((int)$review->getId(), null);
        }

        // dispatch $events
        foreach ($events as $event) {
            $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
        }
    }
}
