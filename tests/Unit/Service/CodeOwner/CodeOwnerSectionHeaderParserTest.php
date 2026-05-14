<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Service\CodeOwner\CodeOwnerSectionHeaderParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(CodeOwnerSectionHeaderParser::class)]
class CodeOwnerSectionHeaderParserTest extends AbstractTestCase
{
    private CodeOwnerSectionHeaderParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CodeOwnerSectionHeaderParser();
    }

    public function testParseInvalidHeader(): void
    {
        static::assertSame([], $this->parser->parse('[Invalid'));
    }

    public function testParseHeaderWithNoOwnersReturnsEmptyArray(): void
    {
        static::assertSame([], $this->parser->parse('[Block 1]'));
    }

    public function testParseHeaderWithSingleOwner(): void
    {
        static::assertSame(['@role/bar'], $this->parser->parse('[Block 2] @role/bar'));
    }

    public function testParseHeaderWithMultipleOwners(): void
    {
        static::assertSame(['@role/foo', '@role/bar'], $this->parser->parse('[Block 3] @role/foo @role/bar'));
    }

    #[TestWith(['[Block 1] # comment'])]
    #[TestWith(['[Block 1]# comment'])]
    public function testParseHeaderInlineCommentIsIgnored(string $line): void
    {
        static::assertSame([], $this->parser->parse($line));
    }
}
