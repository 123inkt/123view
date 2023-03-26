<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetOptimizer;

/**
 * Emphasize the difference of lines next to each other
 */
class UnifiedDiffEmphasizer
{
    public function __construct(private readonly DiffLineChangeSetOptimizer $optimizer)
    {
    }

    public function emphasizeFile(DiffFile $file): DiffFile
    {
        foreach ($file->getBlocks() as $block) {
            $collection = new DiffLineCollection($block->lines);
            foreach ($collection->getDiffLineSet() as $set) {
                if ($set instanceof DiffLineChangeSet) {
                    $this->optimizer->optimize($set);
                }
            }
            $block->lines = $collection->toArray();
        }

        return $file;
    }
}
