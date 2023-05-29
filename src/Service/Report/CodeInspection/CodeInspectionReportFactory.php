<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection;

use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;

class CodeInspectionReportFactory
{
    public function __construct(private readonly CodeInspectionIssueParserProvider $parserProvider)
    {
    }

    public function parse(
        Repository $repository,
        string $commitHash,
        string $inspectionId,
        ?string $branchId,
        string $format,
        string $basePath,
        string $data
    ): CodeInspectionReport {
        $issues = $this->parserProvider->getParser($format)->parse($basePath, $data);

        $report = new CodeInspectionReport();
        $report->setRepository($repository);
        $report->setInspectionId($inspectionId);
        $report->setCommitHash($commitHash);
        foreach ($issues as $issue) {
            $issue->setReport($report);
            $report->getIssues()->add($issue);
        }
        $report->setCreateTimestamp(time());

        return $report;
    }
}
