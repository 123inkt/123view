<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\Comment;

class CommentsViewModel
{
    /**
     * @param array<string, Comment[]> $comments
     * @param Comment[]                $detachedComments
     */
    public function __construct(private readonly array $comments, public readonly array $detachedComments)
    {
    }

    /**
     * @return Comment[]
     */
    public function getComments(DiffLine $line): array
    {
        return $this->comments[spl_object_hash($line)] ?? [];
    }
}
