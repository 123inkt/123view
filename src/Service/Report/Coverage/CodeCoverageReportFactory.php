<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\Coverage;

use DR\Review\Entity\Report\CodeCoverageReport;
use DR\Review\Entity\Repository\Repository;

class CodeCoverageReportFactory
{
    public function __construct(private readonly CodeCoverageParserProvider $parserProvider)
    {
    }

    public function parse(Repository $repository, string $commitHash, string $format, string $basePath, string $data): CodeCoverageReport
    {
        $files = $this->parserProvider->getParser($format)->parse($basePath, $data);

        $report = new CodeCoverageReport();
        $report->setRepository($repository);
        $report->setCommitHash($commitHash);
        foreach ($files as $file) {
            $file->setReport($report);
            $report->getFiles()->add($file);
        }
        $report->setCreateTimestamp(time());

        return $report;
    }
}
