<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Ai\AiCodeReviewService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class AnthropicCodeReviewController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'AI_COMMENT_USER_ID')] private readonly int $userId,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository,
        private readonly MessageBusInterface $bus,
        private readonly AiCodeReviewService $reviewService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/anthropic', self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function redirectReviewRoute(#[MapEntity] CodeReview $review): Response
    {
        if ($review->isAiReviewRequested()) {
            $this->addFlash('warning', 'Claude code review already requested, can only be requested once per review (for now)');

            return $this->refererRedirect(ReviewController::class, ['review' => $review]);
        }

        // remove existing ai comments
        foreach ($review->getComments() as $comment) {
            if ($comment->getUser()->getId() === $this->userId) {
                $review->getComments()->removeElement($comment);
                $this->commentRepository->remove($comment);
            }
        }

        // set flag
        // TODO $review->setAiReviewRequested(true);
        $this->reviewRepository->save($review, true);

        // request code review
        // TODO $this->bus->dispatch(new AiReviewRequested($review->getId(), $this->getUser()->getId()));
        $this->reviewService->requestCodeReview($review);

        $this->addFlash('success', 'Claude code review requested, it may take a few seconds to appear in the review');

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
