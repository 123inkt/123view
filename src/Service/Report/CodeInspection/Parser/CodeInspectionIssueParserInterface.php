<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection\Parser;

use DR\Review\Entity\Report\CodeInspectionIssue;

interface CodeInspectionIssueParserInterface
{
    public function validate(string $data): bool;

    /**
     * @return CodeInspectionIssue[]
     */
    public function parse(string $basePath, string $subDirectory, string $data): array;
}
