<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeInspectionIssue;

class CodeInspectionReportViewModel
{
    /**
     * @param CodeInspectionIssue[] $issues
     */
    public function __construct(public readonly array $issues)
    {
    }

    /**
     * @return array<string, CodeInspectionIssue[]>
     */
    public function getGroupByFile(): array
    {
        $result = [];
        foreach ($this->issues as $issue) {
            $result[$issue->getFile()][] = $issue;
        }

        return $result;
    }
}
