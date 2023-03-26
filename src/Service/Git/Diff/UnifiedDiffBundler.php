<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetBundler;

class UnifiedDiffBundler
{
    public function __construct(private readonly DiffLineChangeSetBundler $setBundler)
    {
    }

    public function bundleFile(DiffFile $file): DiffFile
    {
        foreach ($file->getBlocks() as $block) {
            $block->lines = $this->bundleLines($block->lines);
        }

        return $file;
    }

    /**
     * @param DiffLine[] $lines
     *
     * @return DiffLine[]
     */
    public function bundleLines(array $lines): array
    {
        $result = [];

        foreach ((new DiffLineCollection($lines))->getDiffLineSet() as $set) {
            if ($set instanceof DiffLine) {
                $result[] = [$set];
                continue;
            }

            $bundledLines = $this->setBundler->bundle($set);
            if ($bundledLines === null) {
                $result[] = $set->removed;
                $result[] = $set->added;
            } else {
                $result[] = $bundledLines;
            }
        }

        return array_merge(...$result);
    }
}
