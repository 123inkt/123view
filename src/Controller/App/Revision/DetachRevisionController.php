<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Revision;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\DetachRevisionsFormType;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Webhook\ReviewEventService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DetachRevisionController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly ReviewEventService $eventService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/revisions', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $revisions = $review->getRevisions()->toArray();

        $form = $this->createForm(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            throw new BadRequestHttpException('Submitted invalid form');
        }

        /** @var array<string, bool> $data */
        $data              = $form->getData();
        $detachedRevisions = [];

        foreach ($revisions as $revision) {
            $formKey = 'rev' . $revision->getId();
            if (isset($data[$formKey]) === false || $data[$formKey] === false) {
                continue;
            }
            $revision->setReview(null);
            $review->getRevisions()->removeElement($revision);
            $this->revisionRepository->save($revision);
            $detachedRevisions[] = $revision;
        }

        // save changes
        $this->reviewRepository->save($review, true);

        // notify subscribers
        $this->eventService->revisionsDetached($review, $detachedRevisions);

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
