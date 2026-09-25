<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Review\ChangeReviewStateRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangeReviewStateController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly ReviewEventService $eventService)
    {
    }

    #[Route('app/reviews/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeReviewStateRequest $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $reviewState = $review->getState();
        $this->reviewRepository->save($review->setState($request->getState()), true);

        $this->eventService->reviewStateChanged($review, (string)$reviewState, $this->getUser()->getId());

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
