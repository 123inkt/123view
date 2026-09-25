<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Revision\RevisionVisibilityService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateRevisionVisibilityController extends AbstractController
{
    public function __construct(
        private readonly RevisionVisibilityService $visibilityService,
        private readonly RevisionVisibilityRepository $visibilityRepository,
        private readonly CodeReviewRevisionService $revisionService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/revision-visibility', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $revisions    = $this->revisionService->getRevisions($review);
        $visibilities = $this->visibilityService->getRevisionVisibilities($review, $revisions, $this->getUser());
        foreach ($visibilities as $visibility) {
            $visibility->setVisible(false);
        }

        $form = $this->createForm(RevisionVisibilityFormType::class, ['visibilities' => $visibilities], ['reviewId' => $review->getId()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            throw new BadRequestHttpException('Submitted invalid form');
        }

        $this->visibilityRepository->saveAll($visibilities, true);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
