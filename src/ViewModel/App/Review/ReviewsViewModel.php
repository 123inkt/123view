<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;
use Symfony\Component\Serializer\Attribute\Groups;
use function array_unique;

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
     * @return array<int, string[]>
     */
    #[Groups('app:project-reviews')]
    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->reviews as $review) {
            $reviewId = (int)$review->getId();

            foreach ($review->getRevisions() as $revision) {
                $authors[$reviewId][] = (string)$revision->getAuthorName();
            }
            $authors[$reviewId] = array_unique($authors[$reviewId]);
        }

        return $authors;
    }

    /**
     * @return array<int, string[]>
     */
    #[Groups('app:project-reviews')]
    public function getReviewers(): array
    {
        $reviewers = [];
        foreach ($this->reviews as $review) {
            $reviewId = (int)$review->getId();

            foreach ($review->getReviewers() as $reviewer) {
                $reviewers[$reviewId][] = $reviewer->getUser()->getName();
            }
        }

        return $reviewers;
    }

    /**
     * @return array<int, 'open'|'in-review'|'accepted'|'rejected'|'closed'>
     */
    #[Groups('app:project-reviews')]
    public function getReviewStates(): array
    {
        $reviewStates = [];
        foreach ($this->reviews as $review) {
            $state = $review->getReviewersState();

            if ($review->getReviewers()->count() === 0) {
                $reviewStates[$review->getId()] = $review->getState() ?? 'open';
            } elseif ($state === 'open') {
                $reviewStates[(int)$review->getId()] = 'in-review';
            } else {
                $reviewStates[(int)$review->getId()] = $state;
            }
        }

        return $reviewStates;
    }
}
