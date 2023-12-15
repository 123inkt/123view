<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineReference::class)]
class LineReferenceTest extends AbstractTestCase
{
    public function testFromStringLegacyFormat(): void
    {
        $reference = LineReference::fromString('foo/bar:1:0:1');
        static::assertNull($reference->oldFilePath);
        static::assertSame('foo/bar', $reference->filePath);
        static::assertSame(1, $reference->line);
        static::assertSame(0, $reference->offset);
        static::assertSame(1, $reference->lineAfter);
        static::assertSame(LineReferenceStateEnum::Unknown, $reference->state);
    }

    public function testFromStringCurrentFormat(): void
    {
        $reference = LineReference::fromString('old/path:new/path:1:2:3:A');
        static::assertSame('old/path', $reference->oldFilePath);
        static::assertSame('new/path', $reference->filePath);
        static::assertSame(1, $reference->line);
        static::assertSame(2, $reference->offset);
        static::assertSame(3, $reference->lineAfter);
        static::assertSame(LineReferenceStateEnum::Added, $reference->state);
    }

    public function testFromStringInvalidReference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reference: ');
        LineReference::fromString('foobar');
    }

    public function testToString(): void
    {
        $reference = LineReference::fromString('foo:bar:1:2:3:M');
        static::assertSame('foo:bar:1:2:3:M', (string)$reference);
    }
}
