<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AttachRevisionController extends AbstractController
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewService $reviewService,
        private readonly ReviewEventService $eventService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/attach-revisions', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
    {
        $revisions = $this->revisionRepository->findBy(['id' => array_keys($request->request->all('revision'))]);
        $attach    = $skipped = [];

        foreach ($revisions as $revision) {
            if ($revision->getReview() === null && $revision->getRepository() === $review->getRepository()) {
                $attach[] = $revision;
            } else {
                $skipped[] = $revision;
            }
        }

        if (count($attach) > 0) {
            $this->reviewService->addRevisions($review, $attach);
            $this->eventService->revisionsAdded($review, $revisions, $this->getUser()->getId());

            $this->addFlash(
                'success',
                $this->translator->trans('revisions.added.to.review', ['count' => count($attach), 'review' => 'CR-' . $review->getProjectId()])
            );
        }

        if (count($skipped) > 0) {
            $this->addFlash(
                'warning',
                $this->translator->trans(
                    'revisions.skipped.to.add.to.review',
                    ['count' => count($skipped), 'review' => 'CR-' . $review->getProjectId()]
                )
            );
        }

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
