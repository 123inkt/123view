<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Utils\Arrays;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class CreateBranchReviewController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewCreationService $reviewCreationService,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/review/{repositoryId<\d+>}/branch-review', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity(expr: 'repository.find(repositoryId)')] Repository $repository): Response
    {
        $branchName = (string)$request->request->get('branch', '');
        if (trim($branchName) === '') {
            throw new BadRequestHttpException('Branch request property is mandatory');
        }

        $review = $this->reviewRepository->findOneBy(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => $branchName]);
        if ($review !== null) {
            return $this->redirectToRoute(ReviewController::class, ['review' => $review]);
        }

        $review   = $this->reviewCreationService->createFromBranch($repository, $branchName);
        $revision = Arrays::lastOrNull($this->revisionService->getRevisions($review));
        $this->reviewRepository->save($review, true);

        $this->messageBus->dispatch(new ReviewCreated($review->getId(), (int)$revision?->getId(), $this->getUser()->getId()));

        return $this->redirectToRoute(ReviewController::class, ['review' => $review]);
    }
}
