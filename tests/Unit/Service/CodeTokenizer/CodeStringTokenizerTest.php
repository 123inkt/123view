<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeTokenizer;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeStringTokenizer;
use DR\GitCommitNotification\Service\CodeTokenizer\StringReader;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeTokenizer\CodeStringTokenizer
 */
class CodeStringTokenizerTest extends AbstractTestCase
{
    private CodeStringTokenizer $tokenizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenizer = new CodeStringTokenizer();
    }

    /**
     * @covers ::readString
     */
    public function testReadStringFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting string to start with either " or \'.');
        $this->tokenizer->readString(new StringReader('invalid'));
    }

    /**
     * @covers ::readString
     */
    public function testReadString(): void
    {
        static::assertSame('"foobar"', $this->tokenizer->readString(new StringReader('"foobar"')));
        static::assertSame("'foobar'", $this->tokenizer->readString(new StringReader("'foobar'")));

        static::assertSame('"foobar"', $this->tokenizer->readString(new StringReader('"foobar" test')));
        static::assertSame("'foobar'", $this->tokenizer->readString(new StringReader("'foobar' test")));

        static::assertSame('"foo \' bar"', $this->tokenizer->readString(new StringReader('"foo \' bar"')));
        static::assertSame("'foo \" bar'", $this->tokenizer->readString(new StringReader("'foo \" bar'")));

        static::assertSame('"foo \\" bar"', $this->tokenizer->readString(new StringReader('"foo \\" bar"')));
        static::assertSame("'foo \\' bar'", $this->tokenizer->readString(new StringReader("'foo \\' bar'")));

        static::assertSame('"foo \\\\"', $this->tokenizer->readString(new StringReader('"foo \\\\"')));
        static::assertSame("'foo \\\\'", $this->tokenizer->readString(new StringReader("'foo \\\\'")));
    }
}
