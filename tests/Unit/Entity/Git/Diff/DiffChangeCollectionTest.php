<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffChangeCollection;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffChangeCollection::class)]
class DiffChangeCollectionTest extends AbstractTestCase
{
    public function testAdd(): void
    {
        $emptyChange = new DiffChange(DiffChange::ADDED, '');
        $change      = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        $collection->add($emptyChange);
        $collection->add($change);

        static::assertSame([$change], $collection->toArray());
    }

    public function testAddConcatSimilar(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertEquals([new DiffChange(DiffChange::ADDED, 'foobar')], $collection->toArray());
    }

    public function testLastOrNull(): void
    {
        $collection = new DiffChangeCollection();
        static::assertNull($collection->lastOrNull());
    }

    public function testLastOrNullWithValue(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::REMOVED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeB, $collection->lastOrNull());
    }

    public function testFirst(): void
    {
        $collection = new DiffChangeCollection();
        $this->expectException(LogicException::class);
        $collection->first();
    }

    public function testFirstWithValues(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeA, $collection->first());
    }

    public function testFirstOrNull(): void
    {
        $collection = new DiffChangeCollection();
        static::assertNull($collection->firstOrNull());
    }

    public function testFirstOrNullWithValue(): void
    {
        $changeA = new DiffChange(DiffChange::ADDED, 'foo');
        $changeB = new DiffChange(DiffChange::ADDED, 'bar');

        $collection = new DiffChangeCollection();
        $collection->add($changeA);
        $collection->add($changeB);

        static::assertSame($changeA, $collection->firstOrNull());
    }

    public function testCount(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        static::assertCount(0, $collection);

        $collection->add($change);
        static::assertCount(1, $collection);
    }

    public function testClear(): void
    {
        $change     = new DiffChange(DiffChange::ADDED, 'foobar');
        $collection = new DiffChangeCollection([$change]);

        static::assertCount(1, $collection);

        $collection->clear();
        static::assertCount(0, $collection);
    }

    public function testIterator(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foobar');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame([$change], iterator_to_array($collection->getIterator()));
    }

    public function testGet(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foo');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame($change, $collection->get(0));
    }

    public function testGetShouldThrowExceptionOnOutOfBounds(): void
    {
        $collection = new DiffChangeCollection();

        $this->expectException(LogicException::class);
        $collection->get(0);
    }

    public function testGetOrNull(): void
    {
        $change = new DiffChange(DiffChange::ADDED, 'foo');

        $collection = new DiffChangeCollection();
        $collection->add($change);

        static::assertSame($change, $collection->getOrNull(0));
        static::assertNull($collection->getOrNull(-1));
    }
}
