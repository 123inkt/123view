<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Revision;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;

class RevisionsViewModel
{
    /**
     * @param Paginator<Revision>          $revisions
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        public readonly Repository $repository,
        private readonly Paginator $revisions,
        public readonly PaginatorViewModel $paginator,
        public readonly string $searchQuery
    ) {
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(): array
    {
        return iterator_to_array($this->revisions);
    }
}
