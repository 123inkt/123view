<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLineCollection;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLinePair;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Diff\DiffLineCollection
 * @covers ::__construct
 */
class DiffLineCollectionTest extends AbstractTestCase
{
    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        $lines->remove($lineA);
        static::assertSame([$lineB], $lines->toArray());
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([$lineA, $lineB], $lines->toArray());
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsNoChanges(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([], iterator_to_array($lines->getChangePairs()));
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsOnlyAdditions(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([], iterator_to_array($lines->getChangePairs()));
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsOnlyRemovals(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        static::assertSame([], iterator_to_array($lines->getChangePairs()));
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsAdditionAndRemovalShouldBecomePair(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB]);

        $expected = new DiffLinePair($lineA, $lineB);

        $result = iterator_to_array($lines->getChangePairs());
        static::assertEquals([[$expected]], $result);
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsUnevenAdditionsAndRemovalsShouldBecomePair(): void
    {
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineC = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB, $lineC]);

        $expected = new DiffLinePair($lineA, $lineC);

        $result = iterator_to_array($lines->getChangePairs());
        static::assertEquals([[$expected]], $result);
    }

    /**
     * @covers ::getChangePairs
     */
    public function testGetChangePairsMultipleChangesShouldBecomeMultipleSets(): void
    {
        // should become 2 sets of 1 pair
        $lineA = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineB = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineC = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineE = new DiffLine(DiffLine::STATE_ADDED, []);
        $lines = new DiffLineCollection([$lineA, $lineB, $lineC, $lineD, $lineE]);

        $pairOne = new DiffLinePair($lineA, $lineB);
        $pairTwo = new DiffLinePair($lineD, $lineE);

        $result = iterator_to_array($lines->getChangePairs());
        static::assertEquals([[$pairOne], [$pairTwo]], $result);
    }
}
