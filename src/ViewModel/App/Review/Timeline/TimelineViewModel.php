<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

use Symfony\Component\Serializer\Attribute\Groups;

class TimelineViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param TimelineEntryViewModel[] $entries
     */
    public function __construct(#[Groups('app:timeline')] public array $entries)
    {
    }
}
