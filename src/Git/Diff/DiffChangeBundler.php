<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffChangeCollection;
use DR\GitCommitNotification\Utility\Strings;

/**
 * As --word-diff-regex gives some unexpected behaviour, optimize consecutive DiffChange blocks that have similar starting and ending strings
 * For example:
 *    removed: 'DiffChanceBundler.php'
 *    added:   'DiffChangeBundler.php'
 * Optimize to:
 *    changed: 'DiffChan'
 *    removed: 'c'
 *    added:   'g'
 *    changed: 'eBundler.php
 * @link https://git-scm.com/docs/git-diff#Documentation/git-diff.txt---word-diff-regexltregexgt
 */
class DiffChangeBundler
{
    public function bundle(DiffChangeCollection $changes): DiffChangeCollection
    {
        $result = new DiffChangeCollection();
        $length = count($changes);

        for ($index = 0; $index < $length; $index++) {
            $change = $changes->get($index);
            if ($change->type !== DiffChange::REMOVED) {
                $result->add($change);
                continue;
            }

            $next = $changes->getOrNull($index + 1);
            if ($next === null || $next->type !== DiffChange::ADDED) {
                $result->add($change);
                continue;
            }

            // jump over the next two entries
            $index    += 2;
            $nextNext = $this->getNextNext($changes->getOrNull($index));

            $prefix = Strings::findPrefix($change->code, $next->code);
            if ($prefix !== '') {
                // subtract from current and next change
                $this->mergePrefix($prefix, $this->getPrevious($result), $change, $next);
            }

            $suffix = Strings::findSuffix($change->code, $next->code);
            if ($suffix !== '') {
                // subtract from current and next change
                $this->mergeSuffix($suffix, $change, $next, $nextNext);
            }

            $result->addIfNotEmpty($change);
            $result->addIfNotEmpty($next);
            $result->addIfNotEmpty($nextNext);
        }

        return $result;
    }

    /**
     * Fetch the previous change from the collection, or create and add a new unchanged change
     */
    private function getPrevious(DiffChangeCollection $collection): DiffChange
    {
        $previous = $collection->lastOrNull();
        if ($previous === null || $previous->type !== DiffChange::UNCHANGED) {
            $previous = $collection->add(new DiffChange(DiffChange::UNCHANGED, ''));
        }

        return $previous;
    }

    private function getNextNext(?DiffChange $nextNext): DiffChange
    {
        if ($nextNext === null || $nextNext->type !== DiffChange::UNCHANGED) {
            $nextNext = new DiffChange(DiffChange::UNCHANGED, '');
        }

        return $nextNext;
    }

    /**
     * Subtract the common prefix from `change` and `next` and add it to `previous`
     */
    private function mergePrefix(string $prefix, DiffChange $previous, DiffChange $change, DiffChange $next): void
    {
        $previous->code .= $prefix;
        $change->code = Strings::replacePrefix($change->code, $prefix);
        $next->code   = Strings::replacePrefix($next->code, $prefix);
    }

    /**
     * Subtract the common suffix from `change` and `next` and add it to `nextNext`
     */
    private function mergeSuffix(string $suffix, DiffChange $change, DiffChange $next, DiffChange $nextNext): void
    {
        // subtract from current and next change
        $change->code   = Strings::replaceSuffix($change->code, $suffix);
        $next->code     = Strings::replaceSuffix($next->code, $suffix);
        $nextNext->code = $suffix . $nextNext->code;
    }
}
