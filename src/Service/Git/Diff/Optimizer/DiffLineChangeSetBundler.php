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
                // keep track of the new lines before to keep a correct DiffLine line numbering
                if ($text === DiffLineChangeSet::NEWLINE) {
                    ++$newlinesBefore;
                }
                continue;
            }

            if ($text === DiffLineChangeSet::NEWLINE) {
                $lines[] = $this->createDiffLine($changes, $lineNumbers, $type);
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
            $lines[] = $this->createDiffLine($changes, $lineNumbers, null);
        }

        return $lines;
    }

    /**
     * @param DiffChange[]                               $changes
     *
     * @phpstan-param LineBlockTextIterator::TEXT_*|null $newLineType
     */
    private function createDiffLine(array $changes, DiffLineNumberPair $lineNumbers, ?int $newLineType): DiffLine
    {
        $types = [];
        foreach ($changes as $change) {
            $types[$change->type] = true;
        }

        $lineState = DiffLine::STATE_CHANGED;
        if (count($types) === 0 && $newLineType !== null) {
            // no changes, determine type based on new line type
            $lineState = match ($newLineType) {
                LineBlockTextIterator::TEXT_ADDED   => DiffLine::STATE_ADDED,
                LineBlockTextIterator::TEXT_REMOVED => DiffLine::STATE_REMOVED,
                default                             => DiffLine::STATE_CHANGED,
            };
        } elseif (count($types) === 1) {
            // determine line type based on changes
            $lineState = match (key($types)) {
                DiffChange::ADDED     => DiffLine::STATE_ADDED,
                DiffChange::REMOVED   => DiffLine::STATE_REMOVED,
                DiffChange::UNCHANGED => DiffLine::STATE_UNCHANGED,
                default               => DiffLine::STATE_CHANGED,
            };
        }

        $line                   = new DiffLine($lineState, $changes);
        $line->lineNumberBefore = $lineNumbers->getLineNumberBefore();
        $line->lineNumberAfter  = $lineNumbers->getLineNumberAfter();

        return $line;
    }
}
