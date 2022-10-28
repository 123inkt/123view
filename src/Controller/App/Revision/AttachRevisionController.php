<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewService;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AttachRevisionController extends AbstractController
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CodeReviewService $reviewService,
        private readonly ReviewEventService $eventService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/attach-revisions', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
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
            $this->reviewService->addRevisions($review, $attach, true);
            $this->reviewRepository->save($review, true);
            $this->eventService->revisionsAdded($review, $revisions);

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

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
    }
}
