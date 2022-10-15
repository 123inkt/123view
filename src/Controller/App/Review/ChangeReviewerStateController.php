<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ChangeReviewerStateController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer/state', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $state = $request->request->get('state');

        $userReviewer = $review->getReviewer($this->getUser());
        if ($userReviewer === null) {
            throw new AccessDeniedHttpException('You cant accept a review you\'re not a reviewer on');
        }

        if (in_array($state, [CodeReviewerStateType::ACCEPTED, CodeReviewerStateType::REJECTED], true) === false) {
            throw new BadRequestHttpException('Invalid state value: ' . $state);
        }

        $userReviewer->setState(CodeReviewerStateType::ACCEPTED);
        if ($review->isAccepted()) {
            $review->setState(CodeReviewStateType::CLOSED);
        }

        $em = $this->registry->getManager();
        $em->persist($review);
        $em->persist($userReviewer);
        $em->flush();

        return $this->redirectToRoute(ReviewController::class, ['id' => $review->getId()]);
    }
}
