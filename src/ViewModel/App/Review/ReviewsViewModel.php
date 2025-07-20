<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use Symfony\Component\Serializer\Attribute\Groups;

class ReviewsViewModel
{
    /**
     * @param Paginator<CodeReview>|null          $reviews
     * @param PaginatorViewModel<CodeReview>|null $paginator
     */
    public function __construct(
        #[Groups('app:project-reviews')]
        public readonly ?Repository $repository,
        private readonly ?Paginator $reviews,
        #[Groups('app:project-reviews')]
        public readonly ?PaginatorViewModel $paginator,
        public readonly string $searchQuery,
        public readonly string $searchOrderBy,
        public readonly ?TimelineViewModel $timeline
    ) {
    }

    /**
     * @return CodeReview[]|null
     */
    #[Groups('app:project-reviews')]
    public function getReviews(): ?array
    {
        return $this->reviews === null ? null : iterator_to_array($this->reviews);
    }

    /**
     * @param Collection<int, Revision> $revisions
     *
     * @return string[]
     */
    #[Groups('app:project-reviews')]
    public function getAuthors(Collection $revisions): array
    {
        $authors = [];
        foreach ($revisions as $revision) {
            $authors[] = (string)$revision->getAuthorName();
        }

        return array_unique($authors);
    }
}
