<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

readonly class CommentOverviewViewModel
{
    /**
     * @param list<CommentOverviewItem> $items
     */
    public function __construct(public array $items, public CommentOverviewPaginator $paginator, public string $searchQuery, public string $orderBy)
    {
    }
}
