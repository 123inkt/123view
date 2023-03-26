<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Diff\Optimizer;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetBundler;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetDiffer;
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
        $this->bundler = new DiffLineChangeSetBundler($this->differ);
    }

    public function testOptimizeShouldSkipWhenDiffIsNotPossible(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $this->differ->expects(self::once())->method('diff')->with($set)->willReturn(null);

        static::assertNull($this->bundler->bundle($set));
    }

    public function testBundle(): void
    {
    }
}
