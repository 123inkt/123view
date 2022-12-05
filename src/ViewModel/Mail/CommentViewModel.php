<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Mail;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;

/**
 * @codeCoverageIgnore
 */
class CommentViewModel
{
    /**
     * @param CommentReply[] $replies
     * @param DiffLine[]     $linesBefore
     * @param DiffLine[]     $linesAfter
     */
    public function __construct(
        public readonly string $headingTitle,
        public readonly CodeReview $review,
        public readonly Comment $comment,
        public readonly array $replies,
        public readonly ?DiffFile $file,
        public readonly array $linesBefore,
        public readonly array $linesAfter,
        public readonly ?User $resolvedBy
    ) {
    }
}
