<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection;

use DR\Review\Entity\Report\CodeInspectionIssue;

interface CodeInspectionParserInterface
{
    /**
     * @return CodeInspectionIssue[]
     */
    public function parse(string $data): array;
}
