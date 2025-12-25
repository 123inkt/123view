<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class AiCodeReviewController extends AbstractController
{
    public function __construct(
        #[Autowire(param: 'kernel.debug')] private readonly bool $debug,
        #[Autowire(env: 'AI_COMMENT_USER_ID')] private readonly int $aiUserId,
        private readonly EntityManagerInterface $doctrine,
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/ai', self::class, methods: 'GET', condition: "env('AI_CODE_REVIEW_ENABLED') === 'true'")]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] CodeReview $review): Response
    {
        if ($review->isAiReviewRequested()) {
            $this->addFlash('warning', 'ai.review.already.requested');

            return $this->refererRedirect(ReviewController::class, ['review' => $review]);
        }

        // remove existing ai comments
        foreach ($review->getComments() as $comment) {
            if ($comment->getUser()->getId() === $this->aiUserId) {
                $review->getComments()->removeElement($comment);
                $this->doctrine->remove($comment);
            }
        }

        // set flag (non debug only)
        if ($this->debug === false) {
            $review->setAiReviewRequested(true);
        }
        $this->doctrine->persist($review);
        $this->doctrine->flush();

        // request code review
        $this->bus->dispatch(new AiReviewRequested($review->getId(), $this->getUser()->getId()));

        $this->addFlash('success', 'ai.review.requested');

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
