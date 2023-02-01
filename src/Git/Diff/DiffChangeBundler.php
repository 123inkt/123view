<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff;

use cogpowered\FineDiff\Diff;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Service\Git\Diff\DiffOpcodeTransformer;
use DR\Review\Utility\Strings;

/**
 * Use cogpowered\FineDiff to calculate the word diff for a string without the common prefix and suffix
 */
class DiffChangeBundler
{
    public function __construct(private readonly Diff $diff, private readonly DiffOpcodeTransformer $opcodeTransformer)
    {
    }

    public function bundle(DiffChange $changeBefore, DiffChange $changeAfter): DiffChangeCollection
    {
        $result = new DiffChangeCollection();
        $first  = null;
        $last   = null;

        // find common prefix and split it off
        $prefix = Strings::findPrefix($changeBefore->code, $changeAfter->code);
        if ($prefix !== '') {
            // subtract from current and next change
            $first = new DiffChange(DiffChange::UNCHANGED, '');
            $this->mergePrefix($prefix, $first, $changeBefore, $changeAfter);
        }

        // find common suffix and split it off
        $suffix = Strings::findSuffix($changeBefore->code, $changeAfter->code);
        if ($suffix !== '') {
            // subtract from current and next change
            $last = new DiffChange(DiffChange::UNCHANGED, '');
            $this->mergeSuffix($suffix, $changeBefore, $changeAfter, $last);
        }

        $result->addIfNotEmpty($first);

        $opcodes = $this->diff->getOpcodes($changeBefore->code, $changeAfter->code)->generate();
        $changes = $this->opcodeTransformer->transform($changeBefore->code, $opcodes);
        foreach ($changes as $change) {
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
        $suffixLength = strlen($suffix);

        // if first character of the suffix is delimiter, avoid concat
        if ($suffixLength !== 0
            && $suffixLength !== strlen($changeBefore->code)
            && $suffixLength !== strlen($changeAfter->code)
            && in_array($suffix[0], [' ', ',', ';', '-', '=', '>'], true)) {
            $suffix = substr($suffix, 1);
        }

        // subtract from current and next change
        $changeBefore->code = Strings::replaceSuffix($changeBefore->code, $suffix);
        $changeAfter->code  = Strings::replaceSuffix($changeAfter->code, $suffix);
        $last->code         = $suffix . $last->code;
    }
}
