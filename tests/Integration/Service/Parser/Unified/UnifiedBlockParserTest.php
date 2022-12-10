<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\Parser\Unified;

use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Git\LineReader;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
class UnifiedBlockParserTest extends AbstractTestCase
{
    private UnifiedBlockParser $parser;
    private UnifiedDiffBundler $bundler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser  = new UnifiedBlockParser(new UnifiedLineParser());
        $this->bundler = new UnifiedDiffBundler(new DiffLineComparator(), new DiffChangeBundler(), new DiffLineDiffer());
    }

    public function testParseAdditionsAndDeletions(): void
    {
        $reader = new LineReader(explode("\n", $this->getFileContents('additions-and-deletions.txt')));

        $block        = $this->parser->parse(10, 12, $reader);
        $block->lines = $this->bundler->bundleLines($block->lines);
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
