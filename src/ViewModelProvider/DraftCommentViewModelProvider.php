<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\ViewModel\App\Comment\DraftCommentsViewModel;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Utils\Arrays;

readonly class DraftCommentViewModelProvider
{
    private const PAGE_SIZE = 30;

    public function __construct(private CommentRepository $commentRepository)
    {
    }

    /**
     * @param int<1, max> $page
     */
    public function getDraftCommentsViewModel(User $user, int $page): DraftCommentsViewModel
    {
        $commentPaginator = $this->commentRepository->getDraftsByUser($user, $page, self::PAGE_SIZE);
        $comments         = Arrays::reindex($commentPaginator, static fn(Comment $comment) => (int)$comment->getId());
        $commentsGrouped  = Arrays::groupBy($comments, static fn(Comment $comment) => $comment->getReview()->getId());
        $reviews          = Arrays::mapAssoc($comments, static fn(Comment $comment) => [$comment->getReview()->getId(), $comment->getReview()]);

        /** @var PaginatorViewModel<Comment> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($commentPaginator, $page);

        return new DraftCommentsViewModel($commentsGrouped, $reviews, $paginatorViewModel);
    }
}
