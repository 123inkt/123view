<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use DR\Review\Entity\Git\Diff\DiffLinePair;
use DR\Review\Git\Diff\DiffLineDiffer;

/**
 * Highlight the difference of lines next to each other
 */
class UnifiedDiffEmphasizer
{
    public function __construct(private readonly DiffLineDiffer $differ)
    {
    }

    public function emphasizeFile(DiffFile $file): DiffFile
    {
        foreach ($file->getBlocks() as $block) {
            $block->lines = $this->emphasizeLines($block->lines);
        }

        return $file;
    }

    /**
     * @param DiffLine[] $lines
     *
     * @return DiffLine[]
     */
    public function emphasizeLines(array $lines): array
    {
        $collection = new DiffLineCollection($lines);

        foreach ($collection->getChangePairs() as $pairs) {
            /** @var DiffLinePair $pair */
            foreach ($pairs as $pair) {
                $this->differ->diff($pair->removed, $pair->added);
            }
        }

        return $collection->toArray();
    }
}
