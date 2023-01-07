<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Revision\RevisionVisibilityProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateRevisionVisibilityController extends AbstractController
{
    public function __construct(
        private readonly RevisionVisibilityProvider $visibilityProvider,
        private readonly RevisionVisibilityRepository $visibilityRepository,
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/revision-visibility', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $visibilities = $this->visibilityProvider->getRevisionVisibilities($review, $review->getRevisions(), $this->getUser());

        $form = $this->createForm(RevisionVisibilityFormType::class, ['visibilities' => $visibilities], ['reviewId' => $review->getId()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            throw new BadRequestHttpException('Submitted invalid form');
        }

        $this->visibilityRepository->saveAll($visibilities);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
