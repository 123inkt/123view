<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;

class ReviewsViewModel
{
    /**
     * @param CodeReview[] $reviews
     */
    public function __construct(private readonly array $reviews, private readonly string $searchQuery)
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
        return $this->reviews;
    }
}
