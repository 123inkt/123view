<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception as DBALException;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\UserCommentRepository;
use DR\Review\ViewModel\App\Comment\UserCommentEntryViewModel;
use DR\Review\ViewModel\App\Comment\UserCommentsViewModel;

readonly class UserCommentsViewModelProvider
{
    private const int PAGE_SIZE = 30;

    public function __construct(private UserCommentRepository $userCommentRepository)
    {
    }

    /**
     * @param int<1, max> $page
     *
     * @throws DBALException
     */
    public function getUserCommentsViewModel(
        User $user,
        string $searchQuery,
        string $orderBy,
        int $page,
        ?CommentStateEnum $state = null
    ): UserCommentsViewModel {
        $comments = $this->userCommentRepository->findCommentsAndRepliesByUser($user, $searchQuery, $orderBy, $page, self::PAGE_SIZE, $state);
        $count    = $this->userCommentRepository->countCommentsAndRepliesByUser($user, $searchQuery, $state);

        $entries = array_map(
            static fn(Comment|CommentReply $item): UserCommentEntryViewModel => $item instanceof CommentReply
                ? new UserCommentEntryViewModel($item->getComment(), $item)
                : new UserCommentEntryViewModel($item),
            $comments
        );

        return new UserCommentsViewModel($entries, $searchQuery, $orderBy, $page, (int)ceil($count / self::PAGE_SIZE), $state);
    }
}
