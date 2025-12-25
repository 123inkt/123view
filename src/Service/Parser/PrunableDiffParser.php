<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use DR\Review\Service\Git\Diff\UnifiedDiffPruner;

class PrunableDiffParser
{
    public function __construct(private readonly DiffParser $diffParser, private readonly UnifiedDiffPruner $pruner)
    {
    }

    /**
     * @return DiffFile[]
     * @throws ParseException
     */
    public function parse(string $patch, ?DiffComparePolicy $diffComparePolicy, bool $includeRaw = false): array
    {
        $files = $this->diffParser->parse($patch, $includeRaw);

        if ($diffComparePolicy === DiffComparePolicy::IGNORE_EMPTY_LINES) {
            foreach ($files as $file) {
                $this->pruner->pruneEmptyLines($file);
            }
        }

        return $files;
    }
}
