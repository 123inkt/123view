<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;

class ReviewsViewModel
{
    /**
     * @param Paginator<CodeReview> $reviews
     */
    public function __construct(
        private readonly Repository $repository,
        private readonly Paginator $reviews,
        private readonly PaginatorViewModel $paginator,
        private readonly string $searchQuery
    ) {
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    /**
     * @return CodeReview[]
     */
    public function getReviews(): array
    {
        return iterator_to_array($this->reviews);
    }

    public function getPaginator(): PaginatorViewModel
    {
        return $this->paginator;
    }
}
