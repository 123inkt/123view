<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Reviewer;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\ChangeReviewerStateRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangeReviewerStateController extends AbstractController
{
    public function __construct(private readonly ChangeReviewerStateService $changeReviewerStateService)
    {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeReviewerStateRequest $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $this->changeReviewerStateService->changeState($review, $this->getUser(), $request->getState());

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
