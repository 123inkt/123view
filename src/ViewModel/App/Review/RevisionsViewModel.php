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
