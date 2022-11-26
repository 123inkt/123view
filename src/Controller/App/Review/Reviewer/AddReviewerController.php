<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Reviewer;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewerService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddReviewerController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $codeReviewRepository,
        private readonly CodeReviewerService $reviewerService,
        private readonly ReviewEventService $eventService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $reviewerState = $review->getReviewersState();

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

        $this->eventService->reviewerAdded($review, $reviewer, true);
        $this->eventService->reviewerStateChanged($review, $reviewerState);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
