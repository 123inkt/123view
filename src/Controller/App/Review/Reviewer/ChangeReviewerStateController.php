<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Request\Review\ChangeReviewerStateRequest;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewerService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChangeReviewerStateController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly ReviewEventService $eventService,
        private readonly CodeReviewerService $reviewerService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer/state', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(ChangeReviewerStateRequest $request, CodeReview $review): RedirectResponse
    {
        $state         = $request->getState();
        $reviewState   = (string)$review->getState();
        $reviewerState = $review->getReviewersState();
        $reviewerAdded = false;

        // get reviewer, or assign one
        $userReviewer = $review->getReviewer($this->getUser());
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
        $this->eventService->reviewerAdded($review, $userReviewer, $reviewerAdded);
        $this->eventService->reviewerStateChanged($review, $reviewerState);
        $this->eventService->reviewStateChanged($review, $reviewState);

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
    }
}
