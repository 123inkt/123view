<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser\Unified;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Git\LineReader;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Parser\Unified\UnifiedBlockParser
 * @covers ::__construct
 */
class UnifiedBlockParserTest extends AbstractTestCase
{
    private UnifiedBlockParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new UnifiedBlockParser(new UnifiedLineParser());
    }

    /**
     * @covers ::parse
     */
    public function testParseAdded(): void
    {
        $reader = new LineReader(["+added"]);
        $block  = $this->parser->parse(10, 12, $reader);
        static::assertCount(1, $block->lines);
        self::assertDiffChange(DiffChange::ADDED, 'added', $block->lines[0]->changes->first());
    }

    /**
     * @covers ::parse
     */
    public function testParseRemoved(): void
    {
        $reader = new LineReader(["-removed"]);
        $block  = $this->parser->parse(10, 12, $reader);
        static::assertCount(1, $block->lines);
        self::assertDiffChange(DiffChange::REMOVED, 'removed', $block->lines[0]->changes->first());
    }

    /**
     * @covers ::parse
     */
    public function testParseUnchanged(): void
    {
        $reader = new LineReader([" unchanged"]);
        $block  = $this->parser->parse(10, 12, $reader);
        static::assertCount(1, $block->lines);
        self::assertDiffChange(DiffChange::UNCHANGED, 'unchanged', $block->lines[0]->changes->first());
    }

    /**
     * @covers ::parse
     */
    public function testParseComment(): void
    {
        $reader = new LineReader(["\comment"]);
        $block  = $this->parser->parse(10, 12, $reader);
        static::assertCount(0, $block->lines);
    }

    /**
     * @covers ::parse
     */
    public function testParseSkipEmptyStrings(): void
    {
        $reader = new LineReader([""]);
        $block  = $this->parser->parse(10, 12, $reader);
        static::assertCount(0, $block->lines);
    }

    /**
     * @covers ::parse
     */
    public function testParseLineNumbers(): void
    {
        $reader = new LineReader(
            [
                " unchanged",
                "-removed",
                "+added",
                " unchanged"
            ]
        );

        $block = $this->parser->parse(10, 12, $reader);
        static::assertCount(4, $block->lines);

        // assert change types
        self::assertDiffChange(DiffChange::UNCHANGED, 'unchanged', $block->lines[0]->changes->first());
        self::assertDiffChange(DiffChange::REMOVED, 'removed', $block->lines[1]->changes->first());
        self::assertDiffChange(DiffChange::ADDED, 'added', $block->lines[2]->changes->first());
        self::assertDiffChange(DiffChange::UNCHANGED, 'unchanged', $block->lines[3]->changes->first());

        // assert line numbers
        self::assertLineNumber([10, 12], $block->lines[0]);
        self::assertLineNumber([11, null], $block->lines[1]);
        self::assertLineNumber([null, 13], $block->lines[2]);
        self::assertLineNumber([12, 14], $block->lines[3]);
    }

    /**
     * @param array{?int, ?int} $lineNumbers
     */
    private static function assertLineNumber(array $lineNumbers, DiffLine $line): void
    {
        static::assertSame($lineNumbers, [$line->lineNumberBefore, $line->lineNumberAfter]);
    }

    private static function assertDiffChange(int $type, string $code, DiffChange $change): void
    {
        static::assertSame($type, $change->type);
        static::assertSame($code, $change->code);
    }
}
