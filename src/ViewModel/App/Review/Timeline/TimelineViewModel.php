<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review\Timeline;

class TimelineViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param TimelineEntryViewModel[] $entries
     */
    public function __construct(public array $entries)
    {
    }
}
