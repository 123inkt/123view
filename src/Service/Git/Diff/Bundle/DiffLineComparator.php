<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff\Bundle;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Utility\Strings;

class DiffLineComparator
{
    public function compare(DiffLine $lineA, DiffLine $lineB): DiffLineCompareResult
    {
        $codeA = $lineA->getLine();
        $codeB = $lineB->getLine();

        // calculate the levenshtein without whitespace
        [$removals, $additions] = $this->similarity($codeA, $codeB);

        // calculate the whitespace difference
        $whitespace = abs(strlen((string)preg_replace('/\S/', '', $codeA)) - strlen((string)preg_replace('/\S/', '', $codeB)));

        return new DiffLineCompareResult($removals, $additions, $whitespace);
    }

    /**
     * @return int[]
     */
    private function similarity(string $lineA, string $lineB): array
    {
        // remove whitespace
        $lineA = (string)preg_replace('/\s/', '', $lineA);
        $lineB = (string)preg_replace('/\s/', '', $lineB);

        // find and replace common prefix
        $prefix = Strings::findPrefix($lineA, $lineB);
        $lineA = Strings::replacePrefix($lineA, $prefix);
        $lineB = Strings::replacePrefix($lineB, $prefix);

        // find and replace common suffix
        $suffix = Strings::findSuffix($lineA, $lineB);
        $lineA = Strings::replaceSuffix($lineA, $suffix);
        $lineB = Strings::replaceSuffix($lineB, $suffix);

        return [strlen($lineA), strlen($lineB)];
    }
}
