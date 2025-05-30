<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Optimizer;

use ArrayObject;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetDiffer;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetOptimizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DiffLineChangeSetOptimizer::class)]
class DiffLineChangeSetOptimizerTest extends AbstractTestCase
{
    private DiffLineChangeSetDiffer&MockObject $differ;
    private DiffLineChangeSetOptimizer         $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->differ    = $this->createMock(DiffLineChangeSetDiffer::class);
        $this->optimizer = new DiffLineChangeSetOptimizer($this->differ);
    }

    public function testOptimizeShouldSkipWhenDiffIsNotPossible(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $this->differ->expects($this->once())->method('diff')->with($set, DiffComparePolicy::IGNORE)->willReturn(null);

        static::assertSame($set, $this->optimizer->optimize($set, DiffComparePolicy::IGNORE));
    }

    public function testOptimize(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'remove1')]);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'remove2')]);
        $lineC = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'add1')]);
        $lineD = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'add2')]);
        $set   = new DiffLineChangeSet([$lineA, $lineB], [$lineC, $lineD]);

        $changes = new ArrayObject(
            [
                [LineBlockTextIterator::TEXT_REMOVED, 'removed '],
                [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, 'unchanged'],
                [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, 'unchanged'],
                [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "\n"],
                [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "\n"],
                [LineBlockTextIterator::TEXT_ADDED, 'added'],
            ]
        );
        $this->differ->expects($this->once())->method('diff')->with($set, DiffComparePolicy::IGNORE)->willReturn($changes);

        $set = $this->optimizer->optimize($set, DiffComparePolicy::IGNORE);

        static::assertSame("removed unchanged\n\n", $set->getTextBefore());
        static::assertSame("unchanged\nadded\n", $set->getTextAfter());
    }
}
