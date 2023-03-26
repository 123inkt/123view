<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Entity\Git\Diff\DiffLineNumberPair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiffLineChangeSet::class)]
class DiffLineChangeSetTest extends TestCase
{
    public function testGetTextBefore(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'foo')]);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'bar')]);

        $set = new DiffLineChangeSet([$lineA, $lineB], []);
        static::assertSame("foo\nbar\n", $set->getTextBefore());
    }

    public function testGetTextAfter(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'bar')]);

        $set = new DiffLineChangeSet([], [$lineA, $lineB]);
        static::assertSame("foo\nbar\n", $set->getTextAfter());
    }

    public function testClearChanges(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'foo')]);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $set   = new DiffLineChangeSet([$lineA], [$lineB]);

        $set->clearChanges();

        static::assertCount(0, $lineA->changes);
        static::assertCount(0, $lineB->changes);
    }

    public function testGetLineNumbersEmptySet(): void
    {
        $set = new DiffLineChangeSet([], []);
        static::assertNull($set->getLineNumbers());
    }

    public function testGetLineNumbers(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'foo')]);
        $lineA->lineNumberBefore = 10;
        $lineA->lineNumberAfter  = 20;

        $lineB                   = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'foo')]);
        $lineB->lineNumberBefore = 30;
        $lineB->lineNumberAfter  = 40;

        $set = new DiffLineChangeSet([$lineA], [$lineB]);
        static::assertEquals(new DiffLineNumberPair(10, 40), $set->getLineNumbers());
    }
}
