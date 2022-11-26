<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Parser\Unified;

use DR\GitCommitNotification\Entity\Git\Diff\DiffChange;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedLineParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;
use LogicException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Parser\Unified\UnifiedLineParser
 */
class UnifiedLineParserTest extends AbstractTestCase
{
    /**
     * @covers ::parse
     */
    public function testParseEmptyString(): void
    {
        $parser = new UnifiedLineParser();

        $this->expectException(InvalidArgumentException::class);
        $parser->parse('');
    }

    /**
     * @covers ::parse
     */
    public function testParseInvalidString(): void
    {
        $parser = new UnifiedLineParser();

        $this->expectException(LogicException::class);
        $parser->parse('~');
    }

    /**
     * @covers ::parse
     */
    public function testParseAddition(): void
    {
        $parser = new UnifiedLineParser();
        $line = $parser->parse('+addition');

        static::assertNotNull($line);
        static::assertSame(DiffChange::ADDED, $line->changes->first()->type);
        static::assertSame('addition', $line->changes->first()->code);
    }

    /**
     * @covers ::parse
     */
    public function testParseRemoval(): void
    {
        $parser = new UnifiedLineParser();
        $line = $parser->parse('-removal');

        static::assertNotNull($line);
        static::assertSame(DiffChange::REMOVED, $line->changes->first()->type);
        static::assertSame('removal', $line->changes->first()->code);
    }

    /**
     * @covers ::parse
     */
    public function testParseUnchanged(): void
    {
        $parser = new UnifiedLineParser();
        $line = $parser->parse(' unchanged');

        static::assertNotNull($line);
        static::assertSame(DiffChange::UNCHANGED, $line->changes->first()->type);
        static::assertSame('unchanged', $line->changes->first()->code);
    }

    /**
     * @covers ::parse
     */
    public function testParseComment(): void
    {
        $parser = new UnifiedLineParser();
        static::assertNull($parser->parse('\comment'));
    }
}
