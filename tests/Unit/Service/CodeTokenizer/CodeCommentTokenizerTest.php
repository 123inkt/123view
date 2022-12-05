<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeTokenizer;

use DR\Review\Service\CodeTokenizer\CodeCommentTokenizer;
use DR\Review\Service\CodeTokenizer\StringReader;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\CodeTokenizer\CodeCommentTokenizer
 */
class CodeCommentTokenizerTest extends AbstractTestCase
{
    private CodeCommentTokenizer $tokenizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenizer = new CodeCommentTokenizer();
    }

    /**
     * @covers ::isCommentStart
     */
    public function testIsCommentStart(): void
    {
        static::assertFalse($this->tokenizer->isCommentStart(new StringReader('foobar')));
        static::assertFalse($this->tokenizer->isCommentStart(new StringReader('/foobar')));
        static::assertTrue($this->tokenizer->isCommentStart(new StringReader('//foobar')));
    }

    /**
     * @covers ::readComment
     */
    public function testReadComment(): void
    {
        static::assertSame('', $this->tokenizer->readComment(new StringReader('')));
        static::assertSame('// foo bar', $this->tokenizer->readComment(new StringReader('// foo bar')));
    }
}
