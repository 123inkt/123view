<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

readonly class UserCommentsViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param list<UserCommentEntryViewModel> $comments
     */
    public function __construct(public array $comments, public string $searchQuery, public string $orderBy, public int $page, public int $lastPage)
    {
    }
}
