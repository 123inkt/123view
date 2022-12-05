<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\LineReference;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\Review\Entity\Review\LineReference
 * @covers ::__construct
 */
class LineReferenceTest extends AbstractTestCase
{
    /**
     * @covers ::fromString
     */
    public function testFromString(): void
    {
        $reference = LineReference::fromString('foo/bar:1:2:3');
        static::assertSame('foo/bar', $reference->filePath);
        static::assertSame(1, $reference->line);
        static::assertSame(2, $reference->offset);
        static::assertSame(3, $reference->lineAfter);
    }

    /**
     * @covers ::fromString
     */
    public function testFromStringInvalidReference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reference: ');
        LineReference::fromString('foobar');
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $string = 'foo/bar:1:2:3';

        $reference = LineReference::fromString('foo/bar:1:2:3');
        static::assertSame($string, (string)$reference);
    }
}
