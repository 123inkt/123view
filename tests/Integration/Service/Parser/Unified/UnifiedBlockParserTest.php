<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Integration\Service\Parser\Unified;

use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Git\LineReader;
use DR\GitCommitNotification\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\GitCommitNotification\Service\Git\Diff\UnifiedDiffBundler;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedBlockParser;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedLineParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
class UnifiedBlockParserTest extends AbstractTestCase
{
    private UnifiedBlockParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new UnifiedBlockParser(
            new UnifiedLineParser(),
            new UnifiedDiffBundler(new DiffLineComparator(), new DiffChangeBundler(), new DiffLineDiffer())
        );
    }

    public function testParseAdditionsAndDeletions(): void
    {
        $reader = new LineReader(explode("\n", $this->getFileContents('additions-and-deletions.txt')));

        $block = $this->parser->parse(10, 12, $reader);
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
