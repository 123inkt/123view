<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerFileParser;
use DR\Review\Service\CodeOwner\CodeOwnerLineParser;
use DR\Review\Service\CodeOwner\CodeOwnerSectionHeaderParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeOwnerFileParser::class)]
class CodeOwnerFileParserTest extends AbstractTestCase
{
    private MockObject&CodeOwnerLineParser          $lineParser;
    private MockObject&CodeOwnerSectionHeaderParser $sectionHeaderParser;
    private CodeOwnerFileParser                     $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lineParser          = $this->createMock(CodeOwnerLineParser::class);
        $this->sectionHeaderParser = $this->createMock(CodeOwnerSectionHeaderParser::class);
        $this->parser              = new CodeOwnerFileParser($this->lineParser, $this->sectionHeaderParser);
    }

    public function testParseSkipsEmptyLinesAndComments(): void
    {
        $this->lineParser->expects(static::never())->method('parse');
        $this->sectionHeaderParser->expects(static::never())->method('parse');

        static::assertSame([], $this->parser->parse("# comment\n\n   \n"));
    }

    public function testParseDelegatesToLineParser(): void
    {
        $pattern = new OwnerPattern('*.js', ['@frontend-team']);

        $this->lineParser->expects(static::once())->method('parse')->with('*.js @frontend-team', [])->willReturn($pattern);
        $this->sectionHeaderParser->expects(static::never())->method('parse');

        static::assertEquals([$pattern], $this->parser->parse('*.js @frontend-team'));
    }

    public function testParseFiltersNullResultsFromLineParser(): void
    {
        $this->lineParser->expects(static::once())->method('parse')->with('src/ignored/', [])->willReturn(null);
        $this->sectionHeaderParser->expects(static::never())->method('parse');

        static::assertSame([], $this->parser->parse('src/ignored/'));
    }

    public function testParseDelegatesToSectionHeaderParser(): void
    {
        $pattern = new OwnerPattern('readme.md', ['@role/foo']);

        $this->sectionHeaderParser->expects(static::once())->method('parse')
            ->with('[Block 2] @role/bar')
            ->willReturn(['@role/bar']);

        $this->lineParser->expects(static::once())->method('parse')
            ->with('readme.md @role/foo', ['@role/bar'])
            ->willReturn($pattern);

        static::assertEquals([$pattern], $this->parser->parse("[Block 2] @role/bar\nreadme.md @role/foo"));
    }

    public function testParseLineInSectionWithEmptyDefaultOwners(): void
    {
        $pattern = new OwnerPattern('.gitattributes', ['@role/foo']);

        $this->sectionHeaderParser->expects(static::once())->method('parse')
            ->with('[Block 1]')
            ->willReturn([]);

        $this->lineParser->expects(static::once())->method('parse')
            ->with('.gitattributes @role/foo', [])
            ->willReturn($pattern);

        static::assertEquals([$pattern], $this->parser->parse("[Block 1]\n.gitattributes @role/foo"));
    }
}
