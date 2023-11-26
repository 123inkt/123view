<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser\Unified;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UnifiedLineParser::class)]
class UnifiedLineParserTest extends AbstractTestCase
{
    public function testParseEmptyString(): void
    {
        $parser = new UnifiedLineParser();

        $this->expectException(InvalidArgumentException::class);
        $parser->parse('');
    }

    public function testParseInvalidString(): void
    {
        $parser = new UnifiedLineParser();

        $this->expectException(LogicException::class);
        $parser->parse('~');
    }

    public function testParseAddition(): void
    {
        $parser = new UnifiedLineParser();
        $line   = $parser->parse('+addition');

        static::assertNotNull($line);
        static::assertSame(DiffChange::ADDED, $line->changes->first()->type);
        static::assertSame('addition', $line->changes->first()->code);
    }

    public function testParseRemoval(): void
    {
        $parser = new UnifiedLineParser();
        $line   = $parser->parse('-removal');

        static::assertNotNull($line);
        static::assertSame(DiffChange::REMOVED, $line->changes->first()->type);
        static::assertSame('removal', $line->changes->first()->code);
    }

    public function testParseUnchanged(): void
    {
        $parser = new UnifiedLineParser();
        $line   = $parser->parse(' unchanged');

        static::assertNotNull($line);
        static::assertSame(DiffChange::UNCHANGED, $line->changes->first()->type);
        static::assertSame('unchanged', $line->changes->first()->code);
    }

    public function testParseComment(): void
    {
        $parser = new UnifiedLineParser();
        static::assertNull($parser->parse('\comment'));
    }
}
