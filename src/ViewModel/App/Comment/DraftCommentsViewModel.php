<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;

readonly class DraftCommentsViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param array<int, Comment[]>       $comments [reviewId => Comment[]]
     * @param array<int, CodeReview>      $reviews  [id => Review]
     * @param PaginatorViewModel<Comment> $paginator
     */
    public function __construct(public array $comments, public array $reviews, public PaginatorViewModel $paginator)
    {
    }
}
