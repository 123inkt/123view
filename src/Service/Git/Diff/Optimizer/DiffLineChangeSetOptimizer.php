<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Optimizer;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\JBDiff;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class DiffLineChangeSetOptimizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const TEXT_ADDITIONS = [LineBlockTextIterator::TEXT_ADDED => true, LineBlockTextIterator::TEXT_UNCHANGED_AFTER => true];

    public function __construct(private readonly JBDiff $jbdiff)
    {
    }

    public function optimize(DiffLineChangeSet $set): DiffLineChangeSet
    {
        // only additions or only removals, nothing to optimize
        if (count($set->removed) === 0 || count($set->added) === 0) {
            return $set;
        }

        $text1 = $set->getTextBefore();
        $text2 = $set->getTextAfter();

        try {
            // compare text
            $iterator = $this->jbdiff->compareToIterator($text1, $text2, splitOnNewLines: true);
        } catch (DiffToBigException) {
            $this->logger?->info(sprintf('Diff to big: `%s...` - `%s...`', mb_substr(trim($text1), 0, 50), mb_substr(trim($text2), 0, 50)));

            return $set;
        }

        // remove all changes from the current set
        $set->clearChanges();

        $beforeIndex = 0;
        $afterIndex  = 0;
        foreach ($iterator as [$type, $text]) {
            $addition = self::TEXT_ADDITIONS[$type] ?? false;

            // text is newline, move to next DiffLine
            if ($text === "\n") {
                if ($addition) {
                    ++$afterIndex;
                } else {
                    ++$beforeIndex;
                }
                continue;
            }

            $line = $addition ? ($set->added[$afterIndex] ?? null) : ($set->removed[$beforeIndex] ?? null);
            assert($line !== null);

            switch ($type) {
                case LineBlockTextIterator::TEXT_REMOVED:
                    $line->changes->add(new DiffChange(DiffChange::REMOVED, $text));
                    break;
                case LineBlockTextIterator::TEXT_UNCHANGED_BEFORE:
                case LineBlockTextIterator::TEXT_UNCHANGED_AFTER:
                    $line->changes->add(new DiffChange(DiffChange::UNCHANGED, $text));
                    break;
                case LineBlockTextIterator::TEXT_ADDED:
                    $line->changes->add(new DiffChange(DiffChange::ADDED, $text));
                    break;
                default:
                    throw new RuntimeException('Unknown LineBlockTextIterator type: ' . $type);
            }
        }

        return $set;
    }
}
