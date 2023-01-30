<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Utility\Assert;
use DR\Review\Utility\Strings;

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
    public function bundle(DiffChange $changeBefore, DiffChange $changeAfter): DiffChangeCollection
    {
        $result = new DiffChangeCollection();
        $first  = null;
        $last   = null;

        $prefix = Strings::findPrefix($changeBefore->code, $changeAfter->code);
        if ($prefix !== '') {
            // subtract from current and next change
            $first = new DiffChange(DiffChange::UNCHANGED, '');
            $this->mergePrefix($prefix, $first, $changeBefore, $changeAfter);
        }

        $suffix = Strings::findSuffix($changeBefore->code, $changeAfter->code);
        if ($suffix !== '') {
            // subtract from current and next change
            $last = new DiffChange(DiffChange::UNCHANGED, '');
            $this->mergeSuffix($suffix, $changeBefore, $changeAfter, $last);
        }

        $result->addIfNotEmpty($first);
        foreach ($this->mergeChange($changeBefore, $changeAfter) as $change) {
            $result->addIfNotEmpty($change);
        }
        $result->addIfNotEmpty($last);

        return $result;
    }

    /**
     * Subtract the common prefix from `before` and `after` and add it to `first`
     */
    private function mergePrefix(string $prefix, DiffChange $first, DiffChange $changeBefore, DiffChange $changeAfter): void
    {
        $first->code        .= $prefix;
        $changeBefore->code = Strings::replacePrefix($changeBefore->code, $prefix);
        $changeAfter->code  = Strings::replacePrefix($changeAfter->code, $prefix);
    }

    /**
     * Subtract the common suffix from `before` and `after` and add it to `last`
     */
    private function mergeSuffix(string $suffix, DiffChange $changeBefore, DiffChange $changeAfter, DiffChange $last): void
    {
        // subtract from current and next change
        $changeBefore->code = Strings::replaceSuffix($changeBefore->code, $suffix);
        $changeAfter->code  = Strings::replaceSuffix($changeAfter->code, $suffix);
        $last->code         = $suffix . $last->code;
    }

    /**
     * @return DiffChange[]
     */
    public function mergeChange(DiffChange $before, Diffchange $after): DiffChangeCollection
    {
        $result = new DiffChangeCollection();

        if (mb_strlen($before->code) < mb_strlen($after->code)) {
            $needles     = Assert::isArray(preg_split('/\s+/', $before->code));
            $occurrences = Strings::findAll($after->code, $needles);
            if (count($occurrences) !== count($needles)) {
                return [$before, $after];
            }

            $offset = 0;
            for ($i = 0, $max = count($occurrences); $i < $max; $i++) {
                $needle   = $needles[$i];
                $position = $occurrences[$i];
                $result->addIfNotEmpty(new DiffChange(DiffChange::ADDED, substr($after->code, $offset, $position - $offset)));
                $result->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $needle));
                $offset += $position + strlen($needle);
            }

            $result->addIfNotEmpty(new DiffChange(DiffChange::ADDED, substr($after->code, $offset)));
        } else {
            $needles     = Assert::isArray(preg_split('/\s+/', $after->code));
            $occurrences = Strings::findAll($before->code, $needles);
            if (count($occurrences) !== count($needles)) {
                return [$before, $after];
            }

            $offset = 0;
            for ($i = 0, $max = count($occurrences); $i < $max; $i++) {
                $needle   = $needles[$i];
                $position = $occurrences[$i];
                $result->addIfNotEmpty(new DiffChange(DiffChange::REMOVED, substr($before->code, $offset, $position - $offset)));
                $result->addIfNotEmpty(new DiffChange(DiffChange::UNCHANGED, $needle));
                $offset += $position + strlen($needle);
            }

            $result->addIfNotEmpty(new DiffChange(DiffChange::REMOVED, substr($before->code, $offset)));
        }

        return $result;
    }
}
