<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Webhook\ReviewEventService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DetachRevisionController extends AbstractController
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly ReviewEventService $eventService
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/revisions', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): RedirectResponse
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
        $this->eventService->revisionsDetached($review, $detachedRevisions, $this->getUser()->getId());

        return $this->refererRedirect(ReviewController::class, ['review' => $review]);
    }
}
