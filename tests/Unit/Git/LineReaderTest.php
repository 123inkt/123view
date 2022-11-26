<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Git;

use DR\GitCommitNotification\Git\LineReader;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Git\LineReader
 * @covers ::__construct
 */
class LineReaderTest extends AbstractTestCase
{
    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $lines  = "line1\nline2";
        $reader = new LineReader(explode("\n", $lines));

        static::assertSame($lines, (string)$reader);
    }

    /**
     * @covers ::fromString
     */
    public function testFromString(): void
    {
        $lines  = "line1\nline2";
        $reader = LineReader::fromString($lines);

        static::assertSame($lines, (string)$reader);
    }

    /**
     * @covers ::peek
     */
    public function testPeek(): void
    {
        $lines  = ["line1", "line2"];
        $reader = new LineReader($lines);
        static::assertSame('line2', $reader->peek());

        $reader->next();
        static::assertNull($reader->peek());
    }

    /**
     * @covers ::current
     * @covers ::next
     */
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
