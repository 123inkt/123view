<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeTokenizer;

use DR\Review\Service\CodeTokenizer\StringReader;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\CodeTokenizer\StringReader
 * @covers ::__construct
 */
class StringReaderTest extends AbstractTestCase
{
    /**
     * @covers ::next
     * @covers ::peek
     * @covers ::current
     * @covers ::prev
     * @covers ::eol
     */
    public function testNext(): void
    {
        $reader = new StringReader("foobar");

        static::assertSame('f', $reader->current());
        static::assertFalse($reader->eol());

        static::assertSame('o', $reader->next());
        static::assertSame('o', $reader->next());
        static::assertSame('b', $reader->peek());

        static::assertSame('b', $reader->next());
        static::assertSame('a', $reader->next());
        static::assertSame('r', $reader->next());

        static::assertSame('a', $reader->prev());
        static::assertSame('r', $reader->next());
        static::assertNull($reader->next());

        static::assertTrue($reader->eol());
    }
}
