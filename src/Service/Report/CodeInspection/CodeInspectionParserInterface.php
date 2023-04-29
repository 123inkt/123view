<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection;

use DR\Review\Entity\Report\CodeInspection;

interface CodeInspectionParserInterface
{
    /**
     * @return CodeInspection[]
     */
    public function parse(string $data): array;
}
