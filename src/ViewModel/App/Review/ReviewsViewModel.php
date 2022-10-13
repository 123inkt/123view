<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Review\CodeReview;

class ReviewsViewModel
{
    /**
     * @param Paginator<CodeReview> $reviews
     */
    public function __construct(private readonly Paginator $reviews, private readonly int $page, private readonly string $searchQuery)
    {
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

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLastPage(): int
    {
        return (int)ceil($this->reviews->count() / $this->reviews->getQuery()->getMaxResults());
    }
}
