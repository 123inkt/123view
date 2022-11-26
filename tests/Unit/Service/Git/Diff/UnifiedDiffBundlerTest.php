<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\GitCommitNotification\Service\Git\Diff\UnifiedDiffBundler;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Diff\UnifiedDiffBundler
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
     * @covers ::bundle
     * @covers ::isBundleable
     */
    public function testBundle(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line1')]);
        $line2 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line2')]);
        $line3 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line3')]);
        $line4 = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line4')]);

        $result = $this->bundler->bundle([$line1, $line2, $line3, $line4]);
        static::assertCount(3, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::REMOVED, '2'),
            new DiffChange(DiffChange::ADDED, '3'),
        ];
        static::assertEquals($expected, $result[1]->changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::isBundleable
     */
    public function testBundleAdditionsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line added')]);

        $result = $this->bundler->bundle([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::ADDED, ' added'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::isBundleable
     */
    public function testBundleRemovalsOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line removed')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);

        $result = $this->bundler->bundle([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::REMOVED, ' removed'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::isBundleable
     */
    public function testBundleWhitespaceOnly(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'linewhitespace')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line  whitespace')]);

        $result = $this->bundler->bundle([$line1, $line2]);
        static::assertCount(1, $result);

        $expected = [
            new DiffChange(DiffChange::UNCHANGED, 'line'),
            new DiffChange(DiffChange::ADDED, '  '),
            new DiffChange(DiffChange::UNCHANGED, 'whitespace'),
        ];
        static::assertEquals($expected, $result[0]->changes->toArray());
    }

    /**
     * @covers ::bundle
     * @covers ::isBundleable
     */
    public function testBundleNotBundleable(): void
    {
        $line1 = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'this is the old text')]);
        $line2 = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'but this text is completely different and many changes')]);

        $result = $this->bundler->bundle([$line1, $line2]);
        static::assertCount(2, $result);
    }
}
