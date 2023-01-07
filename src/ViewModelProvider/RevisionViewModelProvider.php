<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityProvider;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use Symfony\Component\Form\FormFactoryInterface;

class RevisionViewModelProvider
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionVisibilityProvider $visibilityProvider,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    public function getRevisionsViewModel(Repository $repository, int $page, string $searchQuery, ?bool $attached = null): RevisionsViewModel
    {
        $paginator = $this->revisionRepository->getPaginatorForSearchQuery((int)$repository->getId(), $page, $searchQuery, $attached);

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return new RevisionsViewModel($repository, $paginator, $paginatorViewModel, $searchQuery);
    }

    /**
     * @param Revision[] $revisions
     */
    public function getRevisionViewModel(CodeReview $review, array $revisions): ReviewRevisionViewModel
    {
        $visibilities = $this->visibilityProvider->getRevisionVisibilities($review, $revisions);

        return new ReviewRevisionViewModel(
            $revisions,
            $this->formFactory
                ->create(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])
                ->createView(),
            $this->formFactory
                ->create(RevisionVisibilityFormType::class, ['visibilities' => $visibilities], ['reviewId' => $review->getId()])
                ->createView()
        );
    }
}
