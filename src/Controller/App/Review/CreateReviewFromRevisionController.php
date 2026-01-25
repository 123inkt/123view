<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class CreateReviewFromRevisionController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewCreationService $reviewCreationService,
        private readonly CodeReviewService $reviewService,
        private readonly ReviewRevisionEventService $eventService,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/review/create-from/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] Revision $revision): Response
    {
        if ($revision->getReview() !== null) {
            throw new BadRequestHttpException('Revision already attached to a review: ' . $revision->getReview()->getId());
        }

        $review = $this->reviewCreationService->createFromRevision($revision);
        $this->reviewService->addRevisions($review, [$revision]);
        $this->eventService->revisionAddedToReview(
            $review,
            $revision,
            true,
            CodeReviewStateType::OPEN,
            CodeReviewerStateType::OPEN,
            $this->getUser()->getId()
        );

        return $this->redirectToRoute(ReviewController::class, ['review' => $review, 'tab' => 'revisions']);
    }
}
