<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Service\Webhook\ReviewEventService;

class ChangeReviewerStateService
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly ReviewEventService $eventService,
        private readonly CodeReviewerService $reviewerService
    ) {
    }

    public function changeState(CodeReview $review, User $user, string $state): void
    {
        $reviewState = (string)$review->getState();

        $reviewReviewerState = $review->getReviewersState();
        $reviewerAdded       = false;

        // get reviewer, or assign one
        $userReviewer  = $review->getReviewer($user);
        $reviewerState = $userReviewer?->getState() ?? CodeReviewerStateType::OPEN;
        if ($userReviewer === null) {
            $userReviewer  = $this->reviewerService->addReviewer($review, $user);
            $reviewerAdded = true;
        }

        // set reviewer state
        $this->reviewerService->setReviewerState($review, $userReviewer, $state);

        $em = $this->registry->getManager();
        $em->persist($review);
        $em->persist($userReviewer);
        $em->flush();

        // dispatch events
        $this->eventService->reviewerAdded($review, $userReviewer, $user->getId(), $reviewerAdded);
        $this->eventService->reviewerStateChanged($review, $userReviewer, $reviewerState);
        $this->eventService->reviewReviewerStateChanged($review, $reviewReviewerState, $user->getId());
        $this->eventService->reviewStateChanged($review, $reviewState, $user->getId());
    }
}
