<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\ChangeReviewerStateRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangeReviewerStateController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly ReviewEventService $eventService,
        private readonly CodeReviewerService $reviewerService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeReviewerStateRequest $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $state       = $request->getState();
        $reviewState = (string)$review->getState();

        $reviewReviewerState = $review->getReviewersState();
        $reviewerAdded       = false;

        // get reviewer, or assign one
        $userReviewer  = $review->getReviewer($this->getUser());
        $reviewerState = $userReviewer?->getState() ?? CodeReviewerStateType::OPEN;
        if ($userReviewer === null) {
            $userReviewer  = $this->reviewerService->addReviewer($review, $this->getUser());
            $reviewerAdded = true;
        }

        // set reviewer state
        $this->reviewerService->setReviewerState($review, $userReviewer, $state);

        $em = $this->registry->getManager();
        $em->persist($review);
        $em->persist($userReviewer);
        $em->flush();

        // dispatch events
        $userId = (int)$this->getUser()->getId();
        $this->eventService->reviewerAdded($review, $userReviewer, $userId, $reviewerAdded);
        $this->eventService->reviewerStateChanged($review, $userReviewer, $reviewerState);
        $this->eventService->reviewReviewerStateChanged($review, $reviewReviewerState, $userId);
        $this->eventService->reviewStateChanged($review, $reviewState, $userId);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
