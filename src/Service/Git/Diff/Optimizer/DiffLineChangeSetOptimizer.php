<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff\Optimizer;

use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class DiffLineChangeSetOptimizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly DiffLineChangeSetDiffer $differ, private readonly DiffLineStateDeterminator $stateDeterminator)
    {
    }

    public function optimize(DiffLineChangeSet $set, DiffComparePolicy $comparePolicy): DiffLineChangeSet
    {
        // compare text
        $iterator = $this->differ->diff($set, $comparePolicy);
        if ($iterator === null) {
            return $set;
        }

        // remove all changes from the current set
        $set->clearChanges();

        $beforeIndex = 0;
        $afterIndex  = 0;
        foreach ($iterator as [$type, $text]) {
            $addition = DiffLineChangeSetDiffer::TEXT_ADDITIONS[$type];

            // text is newline, move to next DiffLine
            if ($text === DiffLineChangeSet::NEWLINE) {
                if ($addition) {
                    ++$afterIndex;
                } else {
                    ++$beforeIndex;
                }
                continue;
            }

            $line = $addition ? ($set->added[$afterIndex] ?? null) : ($set->removed[$beforeIndex] ?? null);
            assert($line !== null);
            self::addChange($line, $type, $text);
        }

        return $set;
    }

    /**
     * @phpstan-param LineBlockTextIterator::TEXT_* $type
     */
    private function addChange(DiffLine $line, int $type, string $text): void
    {
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
            // @codeCoverageIgnoreStart
            default:
                throw new RuntimeException('Unknown LineBlockTextIterator type: ' . $type);
            // @codeCoverageIgnoreEnd
        }
    }
}
