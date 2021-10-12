<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffChangeCollection;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Utility\Strings;
use InvalidArgumentException;

/**
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
    public function diff(DiffLine $removed, DiffLine $added): void
    {
        if (count($removed->changes) !== 1 || count($added->changes) !== 1) {
            throw new InvalidArgumentException('The removed and added DiffLine should only contain one change');
        }

        $removal  = $removed->changes->first()->code;
        $addition = $added->changes->first()->code;

        // find common prefix and subtract
        $prefix   = Strings::findPrefix($removal, $addition);
        $removal  = Strings::replacePrefix($removal, $prefix);
        $addition = Strings::replacePrefix($addition, $prefix);

        // find common suffix and subtract
        $suffix   = Strings::findSuffix($removal, $addition);
        $removal  = Strings::replaceSuffix($removal, $suffix);
        $addition = Strings::replaceSuffix($addition, $suffix);

        if ($removal !== $addition && trim($prefix) === '' && trim($suffix) === '') {
            return;
        }

        // update removal
        if ($removal !== '') {
            $removed->changes = new DiffChangeCollection();
            $removed->changes->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $prefix));
            $removed->changes->add(new DiffChange(DiffChange::REMOVED, $removal));
            $removed->changes->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $suffix));
        }

        // update addition
        if ($addition === '') {
            return;
        }

        $added->changes = new DiffChangeCollection();
        $added->changes->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $prefix));
        $added->changes->add(new DiffChange(DiffChange::ADDED, $addition));
        $added->changes->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $suffix));
    }
}
