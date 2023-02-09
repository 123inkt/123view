<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git\Diff\Opcode;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Git\Diff\Opcode\DiffChangeOptimizer;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Git\Diff\Opcode\DiffChangeOptimizer
 */
class DiffChangeOptimizerTest extends AbstractTestCase
{
    private DiffChangeOptimizer $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optimizer = new DiffChangeOptimizer();
    }

    /**
     * @covers ::optimize
     * @covers ::extractCommonPreSuffix
     * @covers ::extractCommonInfix
     */
    public function testOptimizeWithoutUnchanged(): void
    {
        $changeA = new DiffChange(DiffChange::REMOVED, 'start aaa end');
        $changeB = new DiffChange(DiffChange::ADDED, 'start bbb end');

        $collection = $this->optimizer->optimize([$changeA, $changeB]);
        static::assertCount(4, $collection);

        static::assertSame('start ', $collection->get(0)->code);
        static::assertSame('aaa', $collection->get(1)->code);
        static::assertSame('bbb', $collection->get(2)->code);
        static::assertSame(' end', $collection->get(3)->code);
    }

    /**
     * @covers ::optimize
     * @covers ::extractCommonPreSuffix
     * @covers ::extractCommonInfix
     */
    public function testOptimizeShouldSkipInfixExtraction(): void
    {
        $changeA = new DiffChange(DiffChange::REMOVED, 'foo bar');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = $this->optimizer->optimize([$changeA, $changeB]);
        static::assertCount(2, $collection);

        static::assertSame('foo ', $collection->get(0)->code);
        static::assertSame('bar', $collection->get(1)->code);
    }

    /**
     * @covers ::optimize
     * @covers ::extractCommonPreSuffix
     * @covers ::extractCommonInfix
     */
    public function testOptimizeShouldExtractInfixForReduction(): void
    {
        $changeA = new DiffChange(DiffChange::REMOVED, 'foo bar foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = $this->optimizer->optimize([$changeA, $changeB]);
        static::assertCount(3, $collection);

        static::assertSame('foo ', $collection->get(0)->code);
        static::assertSame('bar', $collection->get(1)->code);
        static::assertSame(' foo', $collection->get(2)->code);
    }

    /**
     * @covers ::optimize
     * @covers ::extractCommonPreSuffix
     * @covers ::extractCommonInfix
     */
    public function testOptimizeShouldExtractInfixForAddition(): void
    {
        $changeA = new DiffChange(DiffChange::REMOVED, 'bar');
        $changeB = new DiffChange(DiffChange::ADDED, 'foo bar foo');

        $collection = $this->optimizer->optimize([$changeA, $changeB]);
        static::assertCount(3, $collection);

        static::assertSame('foo ', $collection->get(0)->code);
        static::assertSame('bar', $collection->get(1)->code);
        static::assertSame(' foo', $collection->get(2)->code);
    }
}
