<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeTokenizer;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeCommentTokenizer;
use DR\GitCommitNotification\Service\CodeTokenizer\CodeStringTokenizer;
use DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer
 * @covers ::__construct
 */
class CodeTokenizerTest extends AbstractTestCase
{
    private CodeTokenizer $tokenizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->tokenizer = new CodeTokenizer(new CodeStringTokenizer(), new CodeCommentTokenizer());
    }

    /**
     * @covers ::tokenize
     */
    public function testTokenize(): void
    {
        static::assertSame([[CodeTokenizer::TOKEN_CODE, 'foobar']], $this->tokenizer->tokenize('foobar'));
    }

    /**
     * @covers ::tokenize
     */
    public function testTokenizeWithString(): void
    {
        static::assertSame(
            [[CodeTokenizer::TOKEN_CODE, 'foo'], [CodeTokenizer::TOKEN_STRING, '"bar"']],
            $this->tokenizer->tokenize('foo"bar"')
        );

        static::assertSame(
            [[CodeTokenizer::TOKEN_CODE, 'foo'], [CodeTokenizer::TOKEN_STRING, '"bar"'], [CodeTokenizer::TOKEN_CODE, 'foo']],
            $this->tokenizer->tokenize('foo"bar"foo')
        );
    }

    /**
     * @covers ::tokenize
     */
    public function testTokenizeWithComment(): void
    {
        static::assertSame(
            [[CodeTokenizer::TOKEN_CODE, 'foo '], [CodeTokenizer::TOKEN_COMMENT, '// bar']],
            $this->tokenizer->tokenize('foo // bar')
        );
    }
}
