<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Optimizer;

use ArrayObject;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Entity\Git\Diff\DiffLineNumberPair;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetBundler;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetDiffer;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineStateDeterminator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DiffLineChangeSetBundler::class)]
class DiffLineChangeSetBundlerTest extends AbstractTestCase
{
    private DiffLineChangeSetDiffer&MockObject $differ;
    private DiffLineChangeSetBundler           $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->differ  = $this->createMock(DiffLineChangeSetDiffer::class);
        $this->bundler = new DiffLineChangeSetBundler($this->differ, new DiffLineStateDeterminator());
    }

    public function testBundleShouldSkipWhenDiffIsNotPossible(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $this->differ->expects($this->once())->method('diff')->with($set, DiffComparePolicy::IGNORE)->willReturn(null);

        static::assertNull($this->bundler->bundle($set, DiffComparePolicy::IGNORE));
    }

    public function testBundle(): void
    {
        $set     = $this->createMock(DiffLineChangeSet::class);
        $changes = new ArrayObject(
            [
                [LineBlockTextIterator::TEXT_REMOVED, 'removed '],
                [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, 'unchanged'],
                [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, 'unchanged'],
                [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "\n"],
                [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "\n"],
                [LineBlockTextIterator::TEXT_REMOVED, "\n"],
                [LineBlockTextIterator::TEXT_ADDED, 'added'],
            ]
        );

        $set->expects($this->once())->method('getLineNumbers')->willReturn(new DiffLineNumberPair(10, 20));
        $this->differ->expects($this->once())->method('diff')->with($set, DiffComparePolicy::IGNORE)->willReturn($changes);

        $lines = $this->bundler->bundle($set, DiffComparePolicy::IGNORE);
        static::assertNotNull($lines);
        static::assertCount(3, $lines);

        static::assertCount(2, $lines[0]->changes);
        static::assertSame(10, $lines[0]->lineNumberBefore);
        static::assertSame(20, $lines[0]->lineNumberAfter);

        static::assertCount(0, $lines[1]->changes);
        static::assertSame(11, $lines[1]->lineNumberBefore);
        static::assertNull($lines[1]->lineNumberAfter);

        static::assertCount(1, $lines[2]->changes);
        static::assertNull($lines[2]->lineNumberBefore);
        static::assertSame(21, $lines[2]->lineNumberAfter);
    }
}
