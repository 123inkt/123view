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
     * @param array<string, string>    $diffLines
     */
    public function __construct(private readonly array $comments, private readonly array $detachedComments, private readonly array $diffLines)
    {
    }

    /**
     * @return Comment[]
     */
    public function getDetachedComments(): array
    {
        return $this->detachedComments;
    }

    /**
     * @return Comment[]
     */
    public function getComments(DiffLine $line): array
    {
        $lineReference = $this->diffLines[spl_object_hash($line)] ?? '';

        return $this->comments[$lineReference] ?? [];
    }
}
