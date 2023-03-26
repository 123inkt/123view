<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Optimizer;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\JBDiff;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DiffLineChangeSetDiffer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const TEXT_ADDITIONS = [
        LineBlockTextIterator::TEXT_REMOVED          => false,
        LineBlockTextIterator::TEXT_UNCHANGED_BEFORE => false,
        LineBlockTextIterator::TEXT_ADDED            => true,
        LineBlockTextIterator::TEXT_UNCHANGED_AFTER  => true,
    ];

    public function __construct(private readonly JBDiff $jbdiff)
    {
    }

    public function diff(DiffLineChangeSet $set): ?LineBlockTextIterator
    {
        // only additions or only removals, nothing to optimize
        if (count($set->removed) === 0 || count($set->added) === 0) {
            return null;
        }

        $text1 = $set->getTextBefore();
        $text2 = $set->getTextAfter();

        try {
            // compare text
            return $this->jbdiff->compareToIterator($text1, $text2, splitOnNewLines: true);
        } catch (DiffToBigException) {
            $this->logger?->info(sprintf('Diff to big: `%s...` - `%s...`', mb_substr(trim($text1), 0, 50), mb_substr(trim($text2), 0, 50)));

            return null;
        }
    }
}
