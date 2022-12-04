<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review\Timeline;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;

class TimelineEntryViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param non-empty-array<CodeReviewActivity> $activities
     */
    public function __construct(public readonly array $activities, public readonly string $message)
    {
    }
}
