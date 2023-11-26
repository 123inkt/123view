<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\Parser\Unified;

use DR\JBDiff\JBDiff;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Git\LineReader;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetBundler;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineChangeSetDiffer;
use DR\Review\Service\Git\Diff\Optimizer\DiffLineStateDeterminator;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class UnifiedBlockParserTest extends AbstractTestCase
{
    private UnifiedBlockParser $parser;
    private UnifiedDiffBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $differ        = new DiffLineChangeSetDiffer(null, new JBDiff());
        $this->parser  = new UnifiedBlockParser(new UnifiedLineParser());
        $this->bundler = new UnifiedDiffBundler(new DiffLineChangeSetBundler($differ, new DiffLineStateDeterminator()));
    }

    public function testParseAdditionsAndDeletions(): void
    {
        $reader = new LineReader(explode("\n", $this->getFileContents('additions-and-deletions.txt')));

        $block        = $this->parser->parse(10, 12, $reader);
        $block->lines = $this->bundler->bundleLines($block->lines, DiffComparePolicy::IGNORE);
        static::assertCount(3, $block->lines);
    }

    public function testParseAdditionsOnly(): void
    {
        $reader = new LineReader(explode("\n", $this->getFileContents('additions-only.txt')));

        $block = $this->parser->parse(10, 12, $reader);
        static::assertCount(4, $block->lines);
    }

    public function testParseRemovalsOnly(): void
    {
        $reader = new LineReader(explode("\n", $this->getFileContents('removals-only.txt')));

        $block = $this->parser->parse(10, 12, $reader);
        static::assertCount(4, $block->lines);
    }
}
