<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class AnthropicCodeReviewController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/anthropic', self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function redirectReviewRoute(#[MapEntity] CodeReview $review): RedirectResponse
    {
        if ($review->isAiReviewRequested()) {
            $this->addFlash('warning', 'Claude code review already requested, can only be requested once per review (for now)');

            return $this->refererRedirect(ReviewController::class, ['review' => $review]);
        }

        // set flag
        $review->setAiReviewRequested(true);
        $this->reviewRepository->save($review, true);

        // request code review
        $this->bus->dispatch(new AiReviewRequested($review->getId(), $this->getUser()->getId()));

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
