<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Reviewer;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class RemoveReviewerController extends AbstractController
{
    public function __construct(private ManagerRegistry $registry, private readonly ReviewEventService $eventService)
    {
    }

    #[Route('app/reviews/{reviewId<\d+>}/reviewer/{reviewerId<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review', expr: 'repository.find(reviewId)')]
    #[Entity('reviewer', expr: 'repository.find(reviewerId)')]
    public function __invoke(CodeReview $review, CodeReviewer $reviewer): RedirectResponse
    {
        $reviewState   = (string)$review->getState();
        $reviewerState = $review->getReviewersState();

        $review->getReviewers()->removeElement($reviewer);
        if ($review->isAccepted()) {
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

        $this->eventService->reviewerStateChanged($review, $reviewerState);
        $this->eventService->reviewStateChanged($review, $reviewState);

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
    }
}
