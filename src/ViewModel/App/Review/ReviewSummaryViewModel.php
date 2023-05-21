<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ReviewSummaryViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly TimelineViewModel $timelineViewModel, public readonly CodeInspectionReportViewModel $reportViewModel)
    {
    }
}
