<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Git;

use DR\Review\Git\LineReader;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineReader::class)]
class LineReaderTest extends AbstractTestCase
{
    public function testToString(): void
    {
        $lines  = "line1\nline2";
        $reader = new LineReader(explode("\n", $lines));

        static::assertSame($lines, (string)$reader);
    }

    public function testFromString(): void
    {
        $lines  = "line1\nline2";
        $reader = LineReader::fromString($lines);

        static::assertSame($lines, (string)$reader);
    }

    public function testPeek(): void
    {
        $lines  = ["line1", "line2"];
        $reader = new LineReader($lines);
        static::assertSame('line2', $reader->peek());

        $reader->next();
        static::assertNull($reader->peek());
    }

    public function testCurrentAndNext(): void
    {
        $lines  = ["line1", "line2"];
        $reader = new LineReader($lines);
        static::assertSame('line1', $reader->current());

        $reader->next();
        static::assertSame('line2', $reader->current());

        $reader->next();
        static::assertNull($reader->current());
    }
}
