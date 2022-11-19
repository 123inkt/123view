<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\DetachRevisionsFormType;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewRevisionViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel;
use Symfony\Component\Form\FormFactoryInterface;

class RevisionViewModelProvider
{
    public function __construct(private readonly RevisionRepository $revisionRepository, private readonly FormFactoryInterface $formFactory)
    {
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
        return new ReviewRevisionViewModel(
            $revisions,
            $this->formFactory
                ->create(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])
                ->createView()
        );
    }
}
