<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review\Timeline;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;

class TimelineEntryViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param CodeReviewActivity[]  $activities
     * @param array<string, object> $data
     */
    public function __construct(public readonly array $activities, public readonly array $data)
    {
    }
}
