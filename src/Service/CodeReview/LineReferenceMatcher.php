<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\LineReference;

class LineReferenceMatcher
{
    /**
     * @param DiffLine[] $lines
     */
    public function exactMatch(array $lines, LineReference $lineReference): ?DiffLine
    {
        foreach ($lines as $line) {
            // comment was added to a newly added line of code.
            if ($lineReference->offset > 0 && $line->state === DiffLine::STATE_ADDED && $line->lineNumberAfter === $lineReference->lineAfter) {
                return $line;
            }

            // comment was added to a removed line of code
            if ($lineReference->lineAfter === 0 && $lineReference->offset === 0 && $line->lineNumberBefore === $lineReference->line) {
                return $line;
            }

            // comment was added to modified line of code
            if ($lineReference->offset === 0
                && $line->lineNumberBefore === $lineReference->line
                && $line->lineNumberAfter === $lineReference->lineAfter) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @param DiffLine[] $lines
     */
    public function bestEffortMatch(array $lines, LineReference $lineReference): ?DiffLine
    {
        $potentialMatch = null;
        $possibleMatch  = null;

        // find best match for the line reference and the line of code
        foreach ($lines as $index => $line) {
            if ($line->state === DiffLine::STATE_ADDED && $line->lineNumberAfter === $lineReference->lineAfter) {
                $potentialMatch = $line;
            }

            if ($line->lineNumberBefore !== $lineReference->line) {
                continue;
            }

            // find the next line with the correct offset. Must have empty lineNumberBefore
            $lineMatch = $lines[$index + $lineReference->offset] ?? null;
            if ($lineMatch === null) {
                return $line;
            }

            if ($lineMatch->lineNumberAfter === null || $lineMatch->lineNumberAfter === $lineReference->lineAfter) {
                return $lineMatch;
            }

            $possibleMatch = $line;
        }

        return $potentialMatch ?? $possibleMatch;
    }
}
