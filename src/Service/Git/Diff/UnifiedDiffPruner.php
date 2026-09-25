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
            $modifiedLines = [];

            foreach ($block->lines as $line) {
                if ($line->state !== DiffLine::STATE_UNCHANGED) {
                    $modifiedLines[] = $line;
                } else {
                    $updateLinesChanged = self::updateModifiedLines($modifiedLines) || $updateLinesChanged;
                    $modifiedLines      = [];
                }
            }

            $updateLinesChanged = self::updateModifiedLines($modifiedLines) || $updateLinesChanged;
        }

        if ($updateLinesChanged) {
            $file->updateLinesChanged();
        }
    }

    /**
     * @param DiffLine[] $lines
     */
    private static function isEmptyLines(array $lines): bool
    {
        foreach ($lines as $line) {
            if (in_array($line->state, self::PRUNE_STATES, true) === false || count($line->changes) !== 1 || $line->getLine() !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param DiffLine[] $lines
     */
    private static function updateModifiedLines(array $lines): bool
    {
        if (count($lines) === 0) {
            return false;
        }

        if (self::isEmptyLines($lines) === false) {
            return false;
        }

        foreach ($lines as $line) {
            $line->state = DiffLine::STATE_UNCHANGED;
        }

        return true;
    }
}
