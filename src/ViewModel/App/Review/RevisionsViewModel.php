<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;

class RevisionsViewModel
{
    /**
     * @param ExternalLink[]               $externalLinks
     * @param Paginator<Revision>          $revisions
     * @param PaginatorViewModel<Revision> $paginator
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly Paginator $revisions,
        private readonly PaginatorViewModel $paginator,
        private readonly array $externalLinks,
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
     * @return ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
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
