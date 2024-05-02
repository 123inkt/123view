<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;

/**
 * Prune all empty lines that were added or removed.
 */
class UnifiedDiffPruner
{
    private const PRUNE_STATES = [DiffLine::STATE_ADDED, DiffLine::STATE_REMOVED];

    public function pruneEmptyLines(DiffFile $file): void
    {
        $updateLinesChanged = false;
        foreach ($file->getBlocks() as $block) {
            foreach ($block->lines as $line) {
                if (in_array($line->state, self::PRUNE_STATES, true) && count($line->changes) === 1 && $line->getLine() === '') {
                    $line->state        = DiffLine::STATE_UNCHANGED;
                    $updateLinesChanged = true;
                }
            }
        }

        if ($updateLinesChanged) {
            $file->updateLinesChanged();
        }
    }
}
