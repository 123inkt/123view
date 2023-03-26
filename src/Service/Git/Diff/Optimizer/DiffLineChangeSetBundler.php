<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Optimizer;

use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Entity\Git\Diff\DiffLineNumberPair;
use DR\Review\Utility\Assert;
use Psr\Log\LoggerAwareTrait;

class DiffLineChangeSetBundler
{
    use LoggerAwareTrait;

    public function __construct(private readonly DiffLineChangeSetDiffer $differ)
    {
    }

    /**
     * @return DiffLine[]|null
     */
    public function bundle(DiffLineChangeSet $set): ?array
    {
        // compare text
        $iterator = $this->differ->diff($set);
        if ($iterator === null) {
            return null;
        }

        $lineNumbers    = Assert::notNull($set->getLineNumbers());
        $lines          = [];
        $changes        = [];
        $newlinesBefore = 0;

        foreach ($iterator as [$type, $text]) {
            if ($type === LineBlockTextIterator::TEXT_UNCHANGED_BEFORE) {
                // keep track of the new lines before to keep a correct diffline line numbering
                if ($text === DiffLineChangeSet::NEWLINE) {
                    ++$newlinesBefore;
                }
                continue;
            }

            if ($text === DiffLineChangeSet::NEWLINE) {
                $lines[] = $this->createDiffLine($changes, $lineNumbers);
                $lineNumbers->increment(DiffLineChangeSetDiffer::TEXT_ADDITIONS[$type]);
                $lineNumbers->increment(false, $newlinesBefore);
                $newlinesBefore = 0;
                $changes        = [];
                continue;
            }

            $changes[] = match ($type) {
                LineBlockTextIterator::TEXT_REMOVED         => new DiffChange(DiffChange::REMOVED, $text),
                LineBlockTextIterator::TEXT_UNCHANGED_AFTER => new DiffChange(DiffChange::UNCHANGED, $text),
                LineBlockTextIterator::TEXT_ADDED           => new DiffChange(DiffChange::ADDED, $text),
            };
        }

        if (count($changes) > 0) {
            $lines[] = $this->createDiffLine($changes, $lineNumbers);
        }

        return $lines;
    }

    /**
     * @param DiffChange[] $changes
     */
    private function createDiffLine(array $changes, DiffLineNumberPair $lineNumbers): DiffLine
    {
        $types = [];
        foreach ($changes as $change) {
            $types[$change->type] = true;
        }

        // determine line type based on changes
        $lineState = DiffLine::STATE_CHANGED;
        if (count($types) === 1) {
            switch (key($types)) {
                case DiffChange::ADDED:
                    $lineState = DiffLine::STATE_ADDED;
                    break;
                case DiffChange::REMOVED:
                    $lineState = DiffLine::STATE_REMOVED;
                    break;
                case DiffChange::UNCHANGED:
                    $lineState = DiffLine::STATE_UNCHANGED;
                    break;
            }
        }

        $line                   = new DiffLine($lineState, $changes);
        $line->lineNumberBefore = $lineNumbers->getLineNumberBefore();
        $line->lineNumberAfter  = $lineNumbers->getLineNumberAfter();

        return $line;
    }
}
