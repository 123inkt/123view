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
     *
     * @param DiffChange[] $changes
     */
    public function optimize(array $changes): DiffChangeCollection
    {
        $collection = new DiffChangeCollection();

        /** @noinspection ForeachInvariantsInspection */
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
            $collection->add($prev, ...$this->extractCommonInfix($current, $next));
            $collection->add($nextNext);
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
        if ($suffix !== '') { // phpcs:ignore
            $changeBefore->code = substr($changeBefore->code, 0, -strlen($suffix));
            $changeAfter->code  = substr($changeAfter->code, 0, -strlen($suffix));
            $last->code         = $suffix . $last->code;
        }
    }

    /**
     * Test if the change before/after is contained within the other. Extract the common infix
     * @return DiffChange[]
     */
    private function extractCommonInfix(DiffChange $changeBefore, DiffChange $changeAfter): array
    {
        $beforeLength = strlen($changeBefore->code);
        $afterLength  = strlen($changeAfter->code);
        if ($beforeLength === 0 || $afterLength === 0) {
            return [$changeBefore, $changeAfter];
        }

        $needle   = $beforeLength < $afterLength ? $changeBefore : $changeAfter;
        $haystack = $beforeLength < $afterLength ? $changeAfter : $changeBefore;
        $position = strpos($haystack->code, $needle->code);
        if ($position === false) {
            return [$changeBefore, $changeAfter];
        }

        return [
            new DiffChange($haystack->type, substr($haystack->code, 0, $position)),
            new DiffChange(DiffChange::UNCHANGED, $needle->code),
            new DiffChange($haystack->type, substr($haystack->code, $position + strlen($needle->code)))
        ];
    }
}
