<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

readonly class CommentOverviewPaginator
{
    public function __construct(public int $page, public int $total, public int $perPage)
    {
    }

    public function getLastPage(): int
    {
        return (int)ceil($this->total / $this->perPage);
    }
}
