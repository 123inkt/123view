<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ChangeReviewerStateController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $registry, private readonly ReviewEventService $eventService)
    {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer/state', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $reviewState   = (string)$review->getState();
        $reviewerState = $review->getReviewersState();

        $state = $request->request->get('state');

        $userReviewer = $review->getReviewer($this->getUser());
        if ($userReviewer === null) {
            throw new AccessDeniedHttpException('You cant accept a review you\'re not a reviewer on');
        }

        if (in_array($state, CodeReviewerStateType::VALUES, true) === false) {
            throw new BadRequestHttpException('Invalid state value: ' . $state);
        }

        $userReviewer->setState($state);
        if ($review->isAccepted()) {
            // resolve all comments
            foreach ($review->getComments() as $comment) {
                $comment->setState(CommentStateType::RESOLVED);
            }
            $review->setState(CodeReviewStateType::CLOSED);
        } else {
            $review->setState(CodeReviewStateType::OPEN);
        }

        $em = $this->registry->getManager();
        $em->persist($review);
        $em->persist($userReviewer);
        $em->flush();

        $this->eventService->reviewerStateChanged($review, $reviewerState);
        $this->eventService->reviewStateChanged($review, $reviewState);

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
    }
}
