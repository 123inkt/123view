<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;

class TimelineEntryViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param non-empty-array<CodeReviewActivity> $activities
     */
    public function __construct(
        public readonly array $activities,
        public readonly string $message,
        public readonly ?Comment $comment,
        public readonly ?string $url
    ) {
    }
}
