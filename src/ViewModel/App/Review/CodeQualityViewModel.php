<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\LineCoverage;

class CodeQualityViewModel
{
    /** @var array<int, CodeInspectionIssue[]> */
    private array $issues;

    private readonly ?LineCoverage $lineCoverage;

    /**
     * @param CodeInspectionIssue[] $issues
     */
    public function __construct(array $issues, private readonly ?CodeCoverageFile $fileCoverage)
    {
        $this->lineCoverage = $this->fileCoverage?->getCoverage();
        $this->issues       = [];

        // create lookup table based on line number
        foreach ($issues as $issue) {
            $this->issues[$issue->getLineNumber()][] = $issue;
        }
    }

    /**
     * @return CodeInspectionIssue[]
     */
    public function getIssues(?int $lineNumber): array
    {
        return $lineNumber === null ? [] : ($this->issues[$lineNumber] ?? []);
    }

    public function getCoveragePercentage(): ?float
    {
        return $this->fileCoverage?->getPercentage() ?? $this->lineCoverage?->getPercentage();
    }

    public function getCoverage(?int $lineNumber): ?int
    {
        if ($this->lineCoverage === null) {
            return null;
        }

        if ($lineNumber === null) {
            return -1;
        }

        return $this->lineCoverage->getCoverage($lineNumber) ?? -1;
    }
}
