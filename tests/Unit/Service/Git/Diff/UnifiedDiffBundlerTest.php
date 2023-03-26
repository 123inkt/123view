<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetBundler;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Diff\UnifiedDiffBundler
 * @covers ::__construct
 */
class UnifiedDiffBundlerTest extends AbstractTestCase
{
    private DiffLineChangeSetBundler&MockObject $setBundler;
    private UnifiedDiffBundler                  $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setBundler = $this->createMock(DiffLineChangeSetBundler::class);
        $this->bundler    = new UnifiedDiffBundler($this->setBundler);
    }

    /**
     * @covers ::bundleFile
     * @covers ::bundleLines
     */
    public function testBundleFile(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line2')]);

        $block        = new DiffBlock();
        $block->lines = [$line1, $line2];
        $file         = new DiffFile();
        $file->addBlock($block);

        $this->setBundler->expects(self::once())->method('bundle')->willReturn([$line1, $line2]);

        static::assertSame($file, $this->bundler->bundleFile($file));
    }

    /**
     * @covers ::bundleLines
     */
    public function testBundleLines(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $this->setBundler->expects(self::once())->method('bundle')->willReturn([$line2]);

        $result = $this->bundler->bundleLines([$line1, $line2, $line3, $line4]);
        static::assertSame([$line1, $line2, $line4], $result);
    }

    /**
     * @covers ::bundleLines
     */
    public function testBundleLinesNotBundleable(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $this->setBundler->expects(self::once())->method('bundle')->willReturn(null);

        $result = $this->bundler->bundleLines([$line1, $line2, $line3, $line4]);
        static::assertSame([$line1, $line2, $line3, $line4], $result);
    }
}
