<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\Coverage\Parser;

use DR\Review\Entity\Report\CodeCoverageFile;

interface CodeCoverageParserInterface
{
    /**
     * @return CodeCoverageFile[]
     */
    public function parse(string $basePath, string $data): array;
}
