<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RemoveReviewerController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly CodeReviewerStateResolver $reviewerStateResolver,
        private readonly ReviewEventService $eventService
    ) {
    }

    #[Route('app/reviews/{reviewId<\d+>}/reviewer/{reviewerId<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(
        #[MapEntity(expr: 'repository.find(reviewId)')] CodeReview $review,
        #[MapEntity(expr: 'repository.find(reviewerId)')] CodeReviewer $reviewer
    ): RedirectResponse {
        $reviewState   = (string)$review->getState();
        $reviewerState = $this->reviewerStateResolver->getReviewersState($review);

        $review->getReviewers()->removeElement($reviewer);
        if ($reviewerState === CodeReviewerStateType::ACCEPTED) {
            // resolve all comments
            foreach ($review->getComments() as $comment) {
                $comment->setState(CommentStateType::RESOLVED);
            }
            $review->setState(CodeReviewStateType::CLOSED);
        }

        $em = $this->registry->getManager();
        $em->remove($reviewer);
        $em->persist($review);
        $em->flush();

        $userId = $this->getUser()->getId();
        $this->eventService->reviewerRemoved($review, $reviewer, $userId);
        $this->eventService->reviewReviewerStateChanged($review, $reviewerState, $userId);
        $this->eventService->reviewStateChanged($review, $reviewState, $userId);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
