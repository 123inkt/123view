<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentOverviewRepository;
use DR\Review\Request\Comment\CommentOverviewRequest;
use DR\Review\ViewModel\App\Comment\CommentOverviewPaginator;
use DR\Review\ViewModel\App\Comment\CommentOverviewViewModel;

readonly class CommentOverviewViewModelProvider
{
    public function __construct(private CommentOverviewRepository $repository)
    {
    }

    public function getCommentOverviewViewModel(User $user, CommentOverviewRequest $request): CommentOverviewViewModel
    {
        $result = $this->repository->getByUser(
            $user,
            $request->getPage(),
            $request->getSearchQuery(),
            $request->getOrderBy()
        );

        return new CommentOverviewViewModel(
            $result['items'],
            new CommentOverviewPaginator($request->getPage(), $result['total'], CommentOverviewRepository::PAGE_SIZE),
            $request->getSearchQuery(),
            $request->getOrderBy()
        );
    }
}
