<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeInspectionIssue;

class CodeInspectionViewModel
{
    /** @var array<int, CodeInspectionIssue[]> */
    private array $issues;

    /**
     * @param CodeInspectionIssue[] $issues
     */
    public function __construct(array $issues)
    {
        $this->issues = [];

        // create lookup table based on line number
        foreach ($issues as $issue) {
            $this->issues[(int)$issue->getLineNumber()][] = $issue;
        }
    }

    /**
     * @return CodeInspectionIssue[]
     */
    public function getIssues(?int $lineNumber): array
    {
        if ($lineNumber === null) {
            return [];
        }

        return $this->issues[$lineNumber] ?? [];
    }
}
