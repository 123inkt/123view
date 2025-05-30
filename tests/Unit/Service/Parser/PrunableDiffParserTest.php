<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use DR\Review\Service\Git\Diff\UnifiedDiffPruner;
use DR\Review\Service\Parser\DiffParser;
use DR\Review\Service\Parser\PrunableDiffParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(PrunableDiffParser::class)]
class PrunableDiffParserTest extends AbstractTestCase
{
    private DiffParser&MockObject        $diffParser;
    private UnifiedDiffPruner&MockObject $pruner;
    private PrunableDiffParser           $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffParser = $this->createMock(DiffParser::class);
        $this->pruner     = $this->createMock(UnifiedDiffPruner::class);
        $this->parser     = new PrunableDiffParser($this->diffParser, $this->pruner);
    }

    /**
     * @throws ParseException
     */
    public function testParseWithPrune(): void
    {
        $file = new DiffFile();

        $this->diffParser->expects($this->once())->method('parse')->with('patch')->willReturn([$file]);
        $this->pruner->expects($this->once())->method('pruneEmptyLines')->with($file);

        $result = $this->parser->parse('patch', DiffComparePolicy::IGNORE_EMPTY_LINES);
        static::assertSame([$file], $result);
    }

    /**
     * @throws ParseException
     */
    public function testParseWithoutPrune(): void
    {
        $file = new DiffFile();

        $this->diffParser->expects($this->once())->method('parse')->with('patch')->willReturn([$file]);
        $this->pruner->expects(self::never())->method('pruneEmptyLines');

        $result = $this->parser->parse('patch', DiffComparePolicy::TRIM);
        static::assertSame([$file], $result);
    }
}
