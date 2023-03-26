<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use InvalidArgumentException;

/**
 * TODO remove
 * Calculate and update the DiffLine to apply a more accurate diff indication while still maintaining a removal and addition line.
 * Example:
 * -This is line one!
 * +This is line two!
 * Result:
 * -This is line {-one-}!
 * +This is line {+two+}!
 */
class DiffLineDiffer
{
    public function __construct(private DiffChangeBundler $changeBundler)
    {
    }

    public function diff(DiffLine $removed, DiffLine $added): void
    {
        if (count($removed->changes) !== 1 || count($added->changes) !== 1) {
            throw new InvalidArgumentException('The removed and added DiffLine should only contain one change');
        }

        $changes = $this->changeBundler->bundle($removed->changes->first(), $added->changes->first());

        $added->changes->clear();
        $removed->changes->clear();

        foreach ($changes as $change) {
            if ($change->type === DiffChange::ADDED) {
                $added->changes->add($change);
            } elseif ($change->type === DiffChange::REMOVED) {
                $removed->changes->add($change);
            } else {
                $removed->changes->add($change);
                $added->changes->add(clone $change);
            }
        }
    }
}
