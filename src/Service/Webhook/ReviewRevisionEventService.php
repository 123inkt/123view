<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class ReviewRevisionEventService
{
    public function __construct(private readonly CodeReviewerStateResolver $reviewerStateResolver, private readonly MessageBusInterface $bus)
    {
    }

    public function revisionAddedToReview(
        CodeReview $review,
        Revision $revision,
        bool $reviewCreated,
        ?string $reviewState,
        string $reviewersState,
        ?int $userId = null
    ): void {
        $events = [];

        // create events
        if ($reviewCreated) {
            $events[] = new ReviewCreated($review->getId(), (int)$revision->getId(), $userId);
        }
        if ($reviewState === CodeReviewStateType::CLOSED && $review->getState() === CodeReviewStateType::OPEN) {
            $events[] = new ReviewOpened($review->getId(), $userId);
        }
        if ($reviewersState !== CodeReviewerStateType::OPEN
            && $this->reviewerStateResolver->getReviewersState($review) === CodeReviewerStateType::OPEN) {
            $events[] = new ReviewResumed($review->getId(), $userId);
        }
        $events[] = new ReviewRevisionAdded($review->getId(), (int)$revision->getId(), $userId, $revision->getTitle());

        // dispatch $events
        foreach ($events as $event) {
            $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
        }
    }

    public function revisionRemovedFromReview(CodeReview $review, Revision $revision, ?string $reviewState): void
    {
        $events   = [];
        $events[] = new ReviewRevisionRemoved($review->getId(), (int)$revision->getId(), null, $revision->getTitle());

        // close review event
        if ($reviewState !== $review->getState()) {
            $events[] = new ReviewClosed($review->getId(), null);
        }

        // dispatch $events
        foreach ($events as $event) {
            $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
        }
    }
}
