<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Arrays;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Utility\Arrays
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
     * @covers ::firstOrNull
     */
    public function testFirstOrNull(): void
    {
        static::assertSame('foo', Arrays::firstOrNull(['foo', 'bar']));
        static::assertNull(Arrays::firstOrNull([]));
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

    /**
     * @covers ::unique
     */
    public function testUnique(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        static::assertSame(['foobar'], Arrays::unique(['foobar', 'foobar']));
        static::assertSame(['foo', 'bar'], Arrays::unique(['foo', 'bar']));
        static::assertSame([1], Arrays::unique([1, 1]));
        static::assertSame([1, '1'], Arrays::unique([1, '1']));
        static::assertSame([$objA, $objB], Arrays::unique([$objA, $objB]));
        static::assertSame([$objA], Arrays::unique([$objA, $objA]));
    }

    /**
     * @covers ::diff
     */
    public function testDiff(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        static::assertSame(['foo'], Arrays::diff(['foo'], ['bar']));
        static::assertSame(['foo'], Arrays::diff(['foo', 'bar'], ['bar']));
        static::assertSame([], Arrays::diff(['foo', 'bar'], ['foo', 'bar']));
        static::assertSame([], Arrays::diff(['foo', 'bar'], ['bar', 'foo']));

        static::assertSame([$objA], Arrays::diff([$objA], [$objB]));
        static::assertSame([], Arrays::diff([$objA], [$objA]));
        static::assertSame([1 => $objB], Arrays::diff([$objA, $objB], [$objA]));
        static::assertSame([], Arrays::diff([$objA, $objB], [$objA, $objB]));
        static::assertSame([], Arrays::diff([$objA, $objB], [$objB, $objA]));
    }
}
