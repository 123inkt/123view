<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use DR\Utils\Arrays;
use Symfony\Component\Form\FormFactoryInterface;

readonly class RevisionViewModelProvider
{
    public function __construct(
        private RevisionRepository $revisionRepository,
        private CodeReviewRepository $reviewRepository,
        private RevisionVisibilityService $visibilityService,
        private RevisionFileRepository $revisionFileRepository,
        private FormFactoryInterface $formFactory,
        private UserEntityProvider $userProvider,
    ) {
    }

    public function getRevisionsViewModel(Repository $repository, int $page, string $searchQuery, ?bool $attached = null): RevisionsViewModel
    {
        $paginator = $this->revisionRepository->getPaginatorForSearchQuery((int)$repository->getId(), $page, $searchQuery, $attached);

        $revisions = iterator_to_array($paginator);
        $reviewIds = array_map(static fn(Revision $revision): ?int => $revision->getReview()?->getId(), $revisions);
        $reviews   = $this->reviewRepository->findBy(['id' => Arrays::removeNull($reviewIds)]);

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return new RevisionsViewModel($repository, $revisions, $reviews, $paginatorViewModel, $searchQuery);
    }

    /**
     * @param Revision[] $revisions
     */
    public function getRevisionViewModel(CodeReview $review, array $revisions): ReviewRevisionViewModel
    {
        $visibilities = $this->visibilityService->getRevisionVisibilities($review, $revisions, $this->userProvider->getCurrentUser());
        $fileChanges  = $this->revisionFileRepository->getFileChanges($revisions);

        if ($review->getType() === CodeReviewType::COMMITS) {
            $detachRevisionForm = $this->formFactory
                ->create(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])
                ->createView();
        } else {
            $detachRevisionForm = null;
        }
        $revisionVisibilityForm = $this->formFactory
            ->create(RevisionVisibilityFormType::class, ['visibilities' => $visibilities], ['reviewId' => $review->getId()])
            ->createView();

        return new ReviewRevisionViewModel($revisions, $fileChanges, $detachRevisionForm, $revisionVisibilityForm);
    }
}
