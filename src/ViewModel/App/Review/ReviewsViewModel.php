<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;

class ReviewsViewModel
{
    /**
     * @param Paginator<CodeReview>          $reviews
     * @param PaginatorViewModel<CodeReview> $paginator
     */
    public function __construct(
        public readonly Repository $repository,
        private readonly Paginator $reviews,
        public readonly PaginatorViewModel $paginator,
        public readonly string $searchQuery
    ) {
    }

    /**
     * @return CodeReview[]
     */
    public function getReviews(): array
    {
        return iterator_to_array($this->reviews);
    }

    /**
     * @param Collection<int, Revision> $revisions
     *
     * @return string[]
     */
    public function getAuthors(Collection $revisions): array
    {
        $authors = [];
        foreach ($revisions as $revision) {
            $authors[] = (string)$revision->getAuthorName();
        }

        return array_unique($authors);
    }
}
