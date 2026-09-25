<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ReviewsViewModel
{
    /**
     * @param CodeReview[]|null                   $reviews
     * @param PaginatorViewModel<CodeReview>|null $paginator
     */
    public function __construct(
        public readonly ?Repository $repository,
        public readonly ?array $reviews,
        public readonly ?PaginatorViewModel $paginator,
        public readonly string $searchQuery,
        public readonly string $searchOrderBy,
        public readonly ?TimelineViewModel $timeline
    ) {
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
