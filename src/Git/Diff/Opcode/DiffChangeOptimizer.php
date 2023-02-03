<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff\Opcode;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Utility\Strings;

class DiffChangeOptimizer
{
    /**
     * The granularity of FineDiff was difficult to fine-tune. A secondary approach worked better to iterate over the changes from FineDiff
     * and subtract the common prefix and suffix.
     */
    public function optimize(array $changes): DiffChangeCollection
    {
        $collection = new DiffChangeCollection();

        for ($i = 0, $len = count($changes); $i < $len; $i++) {
            $current = $changes[$i];
            $next    = $changes[$i + 1] ?? null;

            if ($current->type !== DiffChange::REMOVED || $next?->type !== DiffChange::ADDED) {
                $collection->add($current);
                continue;
            }

            // optimize ADDED and REMOVED diff
            $prev     = new DiffChange(DiffChange::UNCHANGED, '');
            $nextNext = $changes[$i + 2] ?? new DiffChange(DiffChange::UNCHANGED, '');
            $this->extractCommonPreSuffix($prev, $current, $next, $nextNext);
            $collection->add($prev, $current, $next, $nextNext);
            $i += 2;
        }

        return $collection;
    }

    /**
     * Subtract the common prefix from `before` and `after` and add it to `first`
     */
    private function extractCommonPreSuffix(DiffChange $first, DiffChange $changeBefore, DiffChange $changeAfter, DiffChange $last): void
    {
        $prefix = Strings::findPrefix($changeBefore->code, $changeAfter->code);
        if ($prefix !== '') {
            $first->code        .= $prefix;
            $changeBefore->code = substr($changeBefore->code, strlen($prefix));
            $changeAfter->code  = substr($changeAfter->code, strlen($prefix));
        }

        $suffix = Strings::findSuffix($changeBefore->code, $changeAfter->code);
        if ($suffix === '') {
            return;
        }
        $changeBefore->code = substr($changeBefore->code, 0, -strlen($suffix));
        $changeAfter->code  = substr($changeAfter->code, 0, -strlen($suffix));
        $last->code         = $suffix . $last->code;
    }
}
