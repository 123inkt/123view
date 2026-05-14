<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerLineParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(CodeOwnerLineParser::class)]
class CodeOwnerLineParserTest extends AbstractTestCase
{
    private CodeOwnerLineParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CodeOwnerLineParser();
    }

    #[TestWith(['*.js @frontend-team', [], new OwnerPattern('*.js', ['@frontend-team'])])]
    #[TestWith(['*.ts @frontend-team @lead-dev', [], new OwnerPattern('*.ts', ['@frontend-team', '@lead-dev'])])]
    #[TestWith(['src/api/ @backend-team # only backend', [], new OwnerPattern('src/api/', ['@backend-team'])])]
    public function testParseWithExplicitOwners(string $line, array $defaultOwners, OwnerPattern $expected): void
    {
        static::assertEquals($expected, $this->parser->parse($line, $defaultOwners));
    }

    public function testParseWithoutOwnersAndNoDefaultsReturnsNull(): void
    {
        static::assertNull($this->parser->parse('src/ignored/'));
    }

    public function testParseWithoutOwnersUsesDefaultOwners(): void
    {
        $expected = new OwnerPattern('codeowners', ['@role/bar']);
        static::assertEquals($expected, $this->parser->parse('codeowners', ['@role/bar']));
    }

    public function testParseWithoutOwnersStripsInlineComment(): void
    {
        $expected = new OwnerPattern('codeowners', ['@role/bar']);
        static::assertEquals($expected, $this->parser->parse('codeowners # ignored comment', ['@role/bar']));
    }

    public function testParseEmptyLineWithDefaultOwnersReturnsNull(): void
    {
        static::assertNull($this->parser->parse('', ['@role/bar']));
    }
}
