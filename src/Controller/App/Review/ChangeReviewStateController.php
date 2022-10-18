<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ChangeReviewStateController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    #[Route('app/reviews/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $state = $request->request->get('state');
        if (in_array($state, [CodeReviewStateType::OPEN, CodeReviewStateType::CLOSED], true) === false) {
            throw new BadRequestHttpException('Invalid state value: ' . $state);
        }

        $this->reviewRepository->save($review->setState($state), true);

        return $this->redirect($request->server->get('HTTP_REFERER') ?? $this->generateUrl(ReviewController::class, ['id' => $review->getId()]));
    }
}
