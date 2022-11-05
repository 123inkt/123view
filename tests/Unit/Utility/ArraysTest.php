<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Arrays;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Arrays
 */
class ArraysTest extends AbstractTestCase
{
    /**
     * @covers ::first
     */
    public function testFirstThrowsExceptionOnEmptyArray(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to obtain first item from array');
        Arrays::first([]);
    }

    /**
     * @covers ::first
     */
    public function testFirst(): void
    {
        static::assertSame('foo', Arrays::first(['foo', 'bar']));
    }

    /**
     * @covers ::mapAssoc
     */
    public function testMapAssoc(): void
    {
        $callback = static fn($value) => [(string)$value[0], $value[1]];

        static::assertSame([], Arrays::mapAssoc([], $callback));
        static::assertSame(['foo' => 'bar'], Arrays::mapAssoc([['foo', 'bar']], $callback));
    }

    /**
     * @covers ::tryFind
     */
    public function testTryFind(): void
    {
        $objA  = new stdClass();
        $objB  = new stdClass();
        $array = [$objA, $objB];

        static::assertSame($objA, Arrays::tryFind($array, static fn($item) => $item === $objA));
        static::assertSame($objB, Arrays::tryFind($array, static fn($item) => $item === $objB));
        static::assertNull(Arrays::tryFind($array, static fn($item) => false));
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $objA  = new stdClass();
        $objB  = new stdClass();
        $array = [$objA, $objB];

        static::assertSame([1 => $objB], Arrays::remove($array, $objA));
        static::assertSame([0 => $objA], Arrays::remove($array, $objB));
        static::assertSame($array, Arrays::remove($array, 'foobar'));
    }
}
