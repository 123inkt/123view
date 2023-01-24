<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\UnifiedDiffBundler
 * @covers ::__construct
 */
class UnifiedDiffBundlerTest extends AbstractTestCase
{
    private UnifiedDiffBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bundler = new UnifiedDiffBundler(new DiffLineComparator(), new DiffChangeBundler(), new DiffLineDiffer());
    }

    /**
     * @covers ::bundleFile
     */
    public function testBundleFile(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $block        = new DiffBlock();
        $block->lines = [$line1, $line2, $line3, $line4];
        $file         = new DiffFile();
        $file->addBlock($block);

        $result = $this->bundler->bundleFile($file);
        static::assertCount(1, $result->getBlocks());
        static::assertCount(3, $result->getBlocks()[0]->lines);
        $lines = $result->getBlocks()[0]->lines;

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::REMOVED, '2'),
            new DiffChange(DiffChange::ADDED, '3'),
        ];
        static::assertEquals($expected, $lines[1]->changes->toArray());
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     * @covers ::emphasizeDiff
     */
    public function testBundle(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $result = $this->bundler->bundleLines([$line1, $line2, $line3, $line4]);
        static::assertCount(3, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::REMOVED, '2'),
            new DiffChange(DiffChange::ADDED, '3'),
        ];
        static::assertEquals($expected, $result[1]->changes->toArray());
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     * @covers ::emphasizeDiff
     */
    public function testBundleAdditionsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line added')]);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::ADDED, ' added'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     * @covers ::emphasizeDiff
     */
    public function testBundleRemovalsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line removed')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::REMOVED, ' removed'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     * @covers ::emphasizeDiff
     */
    public function testBundleWhitespaceOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'linewhitespace')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line  whitespace')]);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::ADDED, '  '),
            new DiffChange(DiffChange::UNCHANGED, 'whitespace'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundleLines
     * @covers ::isBundleable
     * @covers ::emphasizeDiff
     */
    public function testBundleNotBundleable(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'this is the old text')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'but this text is completely different and many changes')]);

        $result = $this->bundler->bundleLines([$line1, $line2]);
        static::assertCount(2, $result);
    }
}
