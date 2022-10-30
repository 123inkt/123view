<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\Mail;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;

class NewCommentViewModel
{
    /**
     * @param DiffLine[] $linesBefore
     * @param DiffLine[] $linesAfter
     */
    public function __construct(
        public readonly CodeReview $review,
        public readonly Comment $comment,
        public readonly ?DiffFile $file,
        public readonly array $linesBefore,
        public readonly array $linesAfter
    ) {
    }
}
