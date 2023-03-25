<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\JBDiff;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Highlight the difference of lines next to each other
 */
class UnifiedDiffEmphasizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function emphasizeFile(DiffFile $file): DiffFile
    {
        foreach ($file->getBlocks() as $block) {
            $block->lines = $this->emphasizeLines($block->lines);
        }

        return $file;
    }

    /**
     * @param DiffLine[] $lines
     *
     * @return DiffLine[]
     */
    public function emphasizeLines(array $lines): array
    {
        $collection = new DiffLineCollection($lines);

        foreach ($collection->getDiffLineSet() as $set) {
            if (count($set->removed) === 0 || count($set->added) === 0) {
                continue;
            }

            $text1 = $set->getTextBefore();
            $text2 = $set->getTextAfter();

            try {
                $iterator = new LineBlockTextIterator($text1, $text2, JBDiff::compare($text1, $text2));
            } catch (DiffToBigException) {
                $this->logger?->info('Diff to big');
                continue;
            }
            foreach ($set->removed as $line) {
                $line->changes->clear();
            }
            foreach ($set->added as $line) {
                $line->changes->clear();
            }

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

        return $collection->toArray();
    }
}
