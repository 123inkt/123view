<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Reviewer;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\Git\Review\CodeReviewerService;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddReviewerController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $codeReviewRepository,
        private readonly CodeReviewerService $reviewerService,
        private readonly CodeReviewerStateResolver $reviewerStateResolver,
        private readonly ReviewEventService $eventService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $reviewerState = $this->reviewerStateResolver->getReviewersState($review);

        $form = $this->createForm(AddReviewerFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $review]);
        }

        /** @var array<string, User|null> $data */
        $data = $form->getData();
        $user = $data['user'] ?? null;
        if ($user instanceof User === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $review]);
        }

        $reviewer = $this->reviewerService->addReviewer($review, $user);
        $this->codeReviewRepository->save($review, true);

        $userId = $this->getUser()->getId();
        $this->eventService->reviewerAdded($review, $reviewer, $userId, true);
        $this->eventService->reviewReviewerStateChanged($review, $reviewerState, $userId);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
