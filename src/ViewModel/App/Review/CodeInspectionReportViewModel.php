<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeInspectionIssue;

class CodeInspectionReportViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param CodeInspectionIssue[] $issues
     */
    public function __construct(public readonly array $issues)
    {
    }
}
