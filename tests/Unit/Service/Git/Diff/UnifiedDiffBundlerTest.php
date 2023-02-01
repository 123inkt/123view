<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\Bundle\DiffLineCompareResult;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\UnifiedDiffBundler
 * @covers ::__construct
 */
class UnifiedDiffBundlerTest extends AbstractTestCase
{
    private DiffLineComparator&MockObject $comparator;
    private DiffChangeBundler&MockObject  $changeBundler;
    private DiffLineDiffer&MockObject     $differ;
    private UnifiedDiffBundler            $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->comparator    = $this->createMock(DiffLineComparator::class);
        $this->changeBundler = $this->createMock(DiffChangeBundler::class);
        $this->differ        = $this->createMock(DiffLineDiffer::class);
        $this->bundler       = new UnifiedDiffBundler($this->comparator, $this->changeBundler, $this->differ);
    }

    /**
     * @covers ::bundleFile
     * @covers ::emphasizeDiff
     */
    public function testBundleFile(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line2')]);

        $block        = new DiffBlock();
        $block->lines = [$line1, $line2];
        $file         = new DiffFile();
        $file->addBlock($block);

        $comparison = new DiffLineCompareResult(100, 100, 100, 100);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->differ->expects(self::once())->method('diff')->with($line1, $line2);

        static::assertSame($file, $this->bundler->bundleFile($file));
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundle(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $comparison = new DiffLineCompareResult(1, 0, 0, 0);
        $bundled    = new DiffChangeCollection([new DiffChange(DiffChange::ADDED, 'change')]);

        $this->comparator->expects(self::once())->method('compare')->with($line2, $line3)->willReturn($comparison);
        $this->changeBundler->expects(self::once())->method('bundle')->willReturn($bundled);

        $result = $this->bundler->bundleLines([$line1, $line2, $line3, $line4]);
        static::assertCount(3, $result);
        static::assertSame($bundled, $result[1]->changes);
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundleAdditionsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line added')]);

        $comparison = new DiffLineCompareResult(0, 1, 0, 0);
        $bundled    = new DiffChangeCollection([new DiffChange(DiffChange::ADDED, 'change')]);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->changeBundler->expects(self::once())->method('bundle')->willReturn($bundled);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);
        static::assertSame($bundled, $result[0]->changes);
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundleRemovalsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line removed')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);

        $comparison = new DiffLineCompareResult(1, 0, 0, 0);
        $bundled    = new DiffChangeCollection([new DiffChange(DiffChange::REMOVED, 'change')]);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->changeBundler->expects(self::once())->method('bundle')->willReturn($bundled);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);
        static::assertSame($bundled, $result[0]->changes);
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundleWhitespaceOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'linewhitespace')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line  whitespace')]);

        $comparison = new DiffLineCompareResult(0, 0, 1, 0);
        $bundled    = new DiffChangeCollection([new DiffChange(DiffChange::REMOVED, 'change')]);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->changeBundler->expects(self::once())->method('bundle')->willReturn($bundled);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);
        static::assertSame($bundled, $result[0]->changes);
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundleLevenshtein(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'linewhitespace')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line  whitespace')]);

        $comparison = new DiffLineCompareResult(5, 5, 5, 1);
        $bundled    = new DiffChangeCollection([new DiffChange(DiffChange::REMOVED, 'change')]);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->changeBundler->expects(self::once())->method('bundle')->willReturn($bundled);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);
        static::assertSame($bundled, $result[0]->changes);
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     */
    public function testBundleNotBundleable(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'this is the old text')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'but this text is completely different and many changes')]);

        $comparison = new DiffLineCompareResult(500, 500, 500, 500);

        $this->comparator->expects(self::once())->method('compare')->with($line1, $line2)->willReturn($comparison);
        $this->differ->expects(self::once())->method('diff')->with($line1, $line2);
        $this->changeBundler->expects(self::never())->method('bundle');

        $this->bundler->bundleLines([$line1, $line2]);
    }
}
