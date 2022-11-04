<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;

class RevisionsViewModel
{
    /**
     * @param Paginator<Revision>          $revisions
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly Paginator $revisions,
        private readonly PaginatorViewModel $paginator,
        private readonly string $searchQuery
    ) {
    }

    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(): array
    {
        return iterator_to_array($this->revisions);
    }

    /**
     * @return PaginatorViewModel<Revision>
     */
    public function getPaginator(): PaginatorViewModel
    {
        return $this->paginator;
    }
}
