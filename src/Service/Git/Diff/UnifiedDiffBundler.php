<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use DR\Review\Entity\Git\Diff\DiffLinePair;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\Bundle\DiffLineCompareResult;

/**
 * Bundle certain deletions and additions below each other together.
 * -1, +1 line changes
 */
class UnifiedDiffBundler
{
    private DiffChangeBundler  $changeBundler;
    private DiffLineComparator $comparator;
    private DiffLineDiffer     $differ;

    public function __construct(DiffLineComparator $comparator, DiffChangeBundler $changeBundler, DiffLineDiffer $differ)
    {
        $this->comparator    = $comparator;
        $this->changeBundler = $changeBundler;
        $this->differ        = $differ;
    }

    public function bundleFile(DiffFile $file): DiffFile
    {
        foreach ($file->getBlocks() as $block) {
            $block->lines = $this->bundleLines($block->lines);
        }

        return $file;
    }

    /**
     * @param DiffLine[] $lines
     *
     * @return DiffLine[]
     */
    public function bundleLines(array $lines): array
    {
        $collection = new DiffLineCollection($lines);

        foreach ($collection->getChangePairs() as $pairs) {
            /** @var DiffLinePair $pair */
            foreach ($pairs as $pair) {
                if ($this->isBundleable($this->comparator->compare($pair->removed, $pair->added)) !== false) {
                    continue;
                }

                // in a set of multiple pairs, if one of the pairs is not bundleable, skip the whole set
                if (count($pairs) === 1) {
                    // single pair and large difference, emphasize changes in both lines
                    $this->differ->diff($pair->removed, $pair->added);
                }
                continue 2;
            }

            // all pairs are bundleable, bundle now
            foreach ($pairs as $pair) {
                // copy line number from added to removed
                $pair->removed->lineNumberAfter = $pair->added->lineNumberAfter;

                // merge changes into first line, and remove the second line
                $pair->removed->state   = DiffLine::STATE_CHANGED;
                $pair->removed->changes = $this->changeBundler->bundle(DiffChangeCollection::merge($pair->removed->changes, $pair->added->changes));
                $collection->remove($pair->added);
            }
        }

        return $collection->toArray();
    }

    private function isBundleable(DiffLineCompareResult $compareResult): bool
    {
        if ($compareResult->isAdditionsOnly()) {
            return true;
        }

        if ($compareResult->isRemovalOnly()) {
            return true;
        }

        if ($compareResult->isWhitespaceOnly()) {
            return true;
        }

        return $compareResult->additions + $compareResult->removals + $compareResult->whitespace < 40;
    }
}
