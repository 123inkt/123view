<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Review\ReviewsViewModel;

class ReviewsViewModelProvider
{
    private const FEED_EVENTS = [
        ReviewAccepted::NAME,
        ReviewRejected::NAME,
        ReviewOpened::NAME,
        CommentAdded::NAME,
        CommentResolved::NAME,
    ];

    public function __construct(
        private readonly User $user,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider
    ) {
    }

    public function getReviewsViewModel(SearchReviewsRequest $request, Repository $repository): ReviewsViewModel
    {
        $paginator = $this->reviewRepository->getPaginatorForSearchQuery(
            (int)$repository->getId(),
            $request->getPage(),
            $request->getSearchQuery(),
            $request->getOrderBy()
        );

        /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $request->getPage());

        return new ReviewsViewModel(
            $repository,
            $paginator,
            $paginatorViewModel,
            $request->getSearchQuery(),
            $request->getOrderBy(),
            $this->timelineViewModelProvider->getTimelineViewModelForFeed($this->user, self::FEED_EVENTS, $repository)
        );
    }
}
