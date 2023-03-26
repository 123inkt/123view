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

class DiffLineChangeSetOptimizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
            $iterator = $this->jbdiff->compareToIterator($text1, $text2, splitOnNewLines: true);
        } catch (DiffToBigException) {
            $this->logger?->info(sprintf('Diff to big: `%s...` - `%s...`', mb_substr(trim($text1), 0, 50), mb_substr(trim($text2), 0, 50)));

            return $set;
        }

        // remove all changes from the current set
        $set->clearChanges();

        $beforeIndex = 0;
        $afterIndex  = 0;
        foreach ($iterator as [$type, $multiLineText]) {
            $added = in_array($type, [LineBlockTextIterator::TEXT_ADDED, LineBlockTextIterator::TEXT_UNCHANGED_AFTER], true);
            $texts = explode("\n", $multiLineText);

            foreach ($texts as $index => $text) {
                if ($index > 0) {
                    if ($added) {
                        ++$afterIndex;
                    } else {
                        ++$beforeIndex;
                    }
                }
                if ($text === '') {
                    continue;
                }

                $line = $added ? ($set->added[$afterIndex] ?? null) : ($set->removed[$beforeIndex] ?? null);
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
                }
            }
        }
    }

    private static function clearSet(): void
    {
    }
}
