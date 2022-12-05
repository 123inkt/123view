<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

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
