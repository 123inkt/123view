<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;

class ReviewsViewModel
{
    /**
     * @param Paginator<CodeReview>          $reviews
     * @param PaginatorViewModel<CodeReview> $paginator
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

    /**
     * @return PaginatorViewModel<CodeReview>
     */
    public function getPaginator(): PaginatorViewModel
    {
        return $this->paginator;
    }
}
