<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeTokenizer;

use DR\Review\Service\CodeTokenizer\CodeStringTokenizer;
use DR\Review\Service\CodeTokenizer\StringReader;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeStringTokenizer::class)]
class CodeStringTokenizerTest extends AbstractTestCase
{
    private CodeStringTokenizer $tokenizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenizer = new CodeStringTokenizer();
    }

    public function testReadStringFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting string to start with either " or \'.');
        $this->tokenizer->readString(new StringReader('invalid'));
    }

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
