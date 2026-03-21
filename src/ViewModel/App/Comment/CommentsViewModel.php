<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentVisibilityEnum;

class CommentsViewModel
{
    /**
     * @param array<string, Comment[]> $comments
     * @param Comment[]                $detachedComments
     */
    public function __construct(
        private readonly array $comments,
        public readonly array $detachedComments,
        public readonly DiffComparePolicy $comparisonPolicy,
        public readonly CommentVisibilityEnum $commentVisibility
    ) {
    }

    public function isCommentVisible(Comment $comment): bool
    {
        return match ($this->commentVisibility) {
            CommentVisibilityEnum::NONE       => false,
            CommentVisibilityEnum::UNRESOLVED => $comment->getState() === CommentStateType::OPEN,
            default                           => true,
        };
    }

    /**
     * @return Comment[]
     */
    public function getComments(DiffLine $line): array
    {
        return $this->comments[spl_object_hash($line)] ?? [];
    }
}
