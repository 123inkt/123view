<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Optimizer;

use DR\JBDiff\ComparisonPolicy;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\JBDiff;
use DR\JBDiff\LineBlockTextIterator;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetDiffer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(DiffLineChangeSetDiffer::class)]
class DiffLineChangeSetDifferTest extends AbstractTestCase
{
    private JBDiff&MockObject       $jbdiff;
    private DiffLineChangeSetDiffer $differ;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jbdiff = $this->createMock(JBDiff::class);
        $this->differ = new DiffLineChangeSetDiffer(null, $this->jbdiff);
    }

    public function testDiffShouldSkipAdditionOrRemovalOnly(): void
    {
        $set = new DiffLineChangeSet([], []);
        static::assertNull($this->differ->diff($set, DiffComparePolicy::IGNORE));
    }

    public function testDiff(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $iterator = $this->createMock(LineBlockTextIterator::class);

        $this->jbdiff->expects(self::once())
            ->method('compareToIterator')
            ->with("bar\n", "foo\n", ComparisonPolicy::DEFAULT, true)
            ->willReturn($iterator);

        static::assertSame($iterator, $this->differ->diff($set, DiffComparePolicy::IGNORE));
    }

    public function testDiffFailure(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $this->jbdiff->expects(self::once())
            ->method('compareToIterator')
            ->with("bar\n", "foo\n", ComparisonPolicy::DEFAULT, true)
            ->willThrowException(new DiffToBigException());

        static::assertNull($this->differ->diff($set, DiffComparePolicy::IGNORE));
    }
}
