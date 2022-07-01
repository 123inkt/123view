<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Entity\Git\Diff\DiffChangeCollection;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use LogicException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Diff\DiffChangeCollection
 * @covers ::__construct
 */
class DiffChangeCollectionTest extends AbstractTestCase
{
    /**
     * @covers ::addIfNotEmpty
     * @covers ::toArray
     */
    public function testAddIfNotEmpty(): void
    {
        $emptyChange = new DiffChange(DiffChange::ADDED, '');
        $change      = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        $collection->addIfNotEmpty($emptyChange);
        $collection->addIfNotEmpty($change);

        static::assertSame([$change], $collection->toArray());
    }

    /**
     * @covers ::lastOrNull
     */
    public function testLastOrNull(): void
    {
        $collection = new DiffChangeCollection();
        static::assertNull($collection->lastOrNull());
    }

    /**
     * @covers ::lastOrNull
     */
    public function testLastOrNullWithValue(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeB, $collection->lastOrNull());
    }

    /**
     * @covers ::add
     * @covers ::toArray
     */
    public function testAdd(): void
    {
        $emptyChange = new DiffChange(DiffChange::ADDED, '');
        $change      = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        $collection->add($emptyChange);
        $collection->add($change);

        static::assertSame([$emptyChange, $change], $collection->toArray());
    }

    /**
     * @covers ::first
     */
    public function testFirst(): void
    {
        $collection = new DiffChangeCollection();
        $this->expectException(LogicException::class);
        $collection->first();
    }

    /**
     * @covers ::first
     */
    public function testFirstWithValues(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeA, $collection->first());
    }

    /**
     * @covers ::firstOrNull
     */
    public function testFirstOrNull(): void
    {
        $collection = new DiffChangeCollection();
        static::assertNull($collection->firstOrNull());
    }

    /**
     * @covers ::firstOrNull
     */
    public function testFirstOrNullWithValue(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeA, $collection->firstOrNull());
    }

    /**
     * @covers ::merge
     */
    public function testMerge(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collectionA = new DiffChangeCollection([$changeA]);
        $collectionB = new DiffChangeCollection([$changeB]);

        $result = DiffChangeCollection::merge($collectionA, $collectionB);
        static::assertSame([$changeA, $changeB], $result->toArray());
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        static::assertCount(0, $collection);

        $collection->add($change);
        static::assertCount(1, $collection);
    }

    /**
     * @covers ::getIterator
     */
    public function testIterator(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame([$change], iterator_to_array($collection->getIterator()));
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foo');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame($change, $collection->get(0));
    }

    /**
     * @covers ::get
     */
    public function testGetShouldThrowExceptionOnOutOfBounds(): void
    {
        $collection = new DiffChangeCollection();

        $this->expectException(LogicException::class);
        $collection->get(0);
    }

    /**
     * @covers ::getOrNull
     */
    public function testGetOrNull(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foo');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame($change, $collection->getOrNull(0));
        static::assertNull($collection->getOrNull(-1));
    }
}
