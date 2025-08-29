<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Service\User\UserService;
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
        CommentReplyAdded::NAME
    ];

    public function __construct(
        private readonly UserService $userService,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider
    ) {
    }

    public function getSearchReviewsViewModel(SearchReviewsRequest $request, ?TermInterface $terms): ReviewsViewModel
    {
        $paginator          = null;
        $paginatorViewModel = null;

        if ($terms !== null) {
            $paginator = $this->reviewRepository->getPaginatorForSearchQuery(
                null,
                $request->getPage(),
                $terms,
                $request->getOrderBy()
            );
            /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
            $paginatorViewModel = new PaginatorViewModel($paginator, $request->getPage());
        }

        return new ReviewsViewModel(null, $paginator, $paginatorViewModel, $request->getSearchQuery(), $request->getOrderBy(), null);
    }

    public function getReviewsViewModel(SearchReviewsRequest $request, ?TermInterface $terms, Repository $repository): ReviewsViewModel
    {
        $paginator          = null;
        $paginatorViewModel = null;

        if ($terms !== null) {
            $paginator = $this->reviewRepository->getPaginatorForSearchQuery(
                (int)$repository->getId(),
                $request->getPage(),
                $terms,
                $request->getOrderBy()
            );
            /** @var PaginatorViewModel<CodeReview> $paginatorViewModel */
            $paginatorViewModel = new PaginatorViewModel($paginator, $request->getPage());
        }

        return new ReviewsViewModel(
            $repository,
            $paginator,
            $paginatorViewModel,
            $request->getSearchQuery(),
            $request->getOrderBy(),
            $this->timelineViewModelProvider->getTimelineViewModelForFeed($this->userService->getCurrentUser(), self::FEED_EVENTS, $repository)
        );
    }
}
