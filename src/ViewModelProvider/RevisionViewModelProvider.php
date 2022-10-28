<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\ViewModel\App\Review\PaginatorViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\RevisionsViewModel;

class RevisionViewModelProvider
{
    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly ExternalLinkRepository $externalLinkRepository
    ) {
    }

    public function getRevisionViewModel(Repository $repository, int $page, string $searchQuery): RevisionsViewModel
    {
        $paginator = $this->revisionRepository->getPaginatorForSearchQuery((int)$repository->getId(), $page, $searchQuery);
        $externalLinks = $this->externalLinkRepository->findAll();

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return new RevisionsViewModel($repository, $paginator, $paginatorViewModel, $externalLinks, $searchQuery);
    }
}
