<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\LineReference;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineReference::class)]
class LineReferenceTest extends AbstractTestCase
{
    public function testFromString(): void
    {
        $reference = LineReference::fromString('foo/bar:1:2:3');
        static::assertSame('foo/bar', $reference->filePath);
        static::assertSame(1, $reference->line);
        static::assertSame(2, $reference->offset);
        static::assertSame(3, $reference->lineAfter);
    }

    public function testFromStringInvalidReference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reference: ');
        LineReference::fromString('foobar');
    }

    public function testToString(): void
    {
        $string = 'foo/bar:1:2:3';

        $reference = LineReference::fromString('foo/bar:1:2:3');
        static::assertSame($string, (string)$reference);
    }
}
