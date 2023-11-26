<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Git\Diff\DiffLineChangeSet;
use DR\Review\Entity\Git\Diff\DiffLineCollection;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffLineCollection::class)]
class DiffLineCollectionTest extends AbstractTestCase
{
    public function testToArray(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([$lineA, $lineB], $lines->toArray());
    }

    public function testGetChangePairsNoChanges(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([$lineA, $lineB], $lines->getDiffLineSet());
    }

    public function testGetChangePairsOnlyAdditions(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        $expected = new DiffLineChangeSet([], [$lineA, $lineB]);

        static::assertEquals([$expected], $lines->getDiffLineSet());
    }

    public function testGetChangePairsOnlyRemovals(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        $expected = new DiffLineChangeSet([$lineA, $lineB], []);

        static::assertEquals([$expected], $lines->getDiffLineSet());
    }

    public function testGetChangePairsAdditionAndRemovalShouldBecomePair(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        $expected = new DiffLineChangeSet([$lineA], [$lineB]);

        $result = $lines->getDiffLineSet();
        static::assertEquals([$expected], $result);
    }

    public function testGetChangePairsUnevenAdditionsAndRemovalsShouldBecomePair(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineC = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB, $lineC]);

        $expected = new DiffLineChangeSet([$lineA, $lineB], [$lineC]);

        $result = $lines->getDiffLineSet();
        static::assertEquals([$expected], $result);
    }

    public function testGetChangePairsMultipleChangesShouldBecomeMultipleSets(): void
    {
        // should become 2 sets of 1 pair
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineC = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineE = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB, $lineC, $lineD, $lineE]);

        $setOne = new DiffLineChangeSet([$lineA], [$lineB]);
        $setTwo = new DiffLineChangeSet([$lineA], [$lineB]);

        $result = $lines->getDiffLineSet();
        static::assertEquals([$setOne, $lineC, $setTwo], $result);
    }
}
