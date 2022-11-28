<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Request\Review\ChangeReviewStateRequest;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChangeReviewStateController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly ReviewEventService $eventService)
    {
    }

    #[Route('app/reviews/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('review')]
    public function __invoke(ChangeReviewStateRequest $request, CodeReview $review): RedirectResponse
    {
        $reviewState = $review->getState();
        $this->reviewRepository->save($review->setState($request->getState()), true);

        $this->eventService->reviewStateChanged($review, (string)$reviewState);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
