<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Parser\DiffFileParser;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversNothing
 */
class DiffFileParserTest extends AbstractTestCase
{
    private DiffFileParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $blockParser  = new UnifiedBlockParser(
            new UnifiedLineParser(),
            new UnifiedDiffBundler(new DiffLineComparator(), new DiffChangeBundler(), new DiffLineDiffer())
        );
        $this->parser = new DiffFileParser($blockParser);
    }

    public function testParse(): void
    {
        $contents                 = $this->getFileContents('deletions-and-additions.txt');
        $fileDiff                 = new DiffFile();
        $fileDiff->filePathBefore = 'example.yml';
        $fileDiff->filePathAfter  = 'example.yml';

        // expect 2 blocks
        $fileDiff = $this->parser->parse($contents, $fileDiff);
        static::assertCount(2, $fileDiff->getBlocks());

        // expect each block have 3 lines
        [$blockA, $blockB] = $fileDiff->getBlocks();
        static::assertCount(3, $blockA->lines);
        static::assertCount(3, $blockB->lines);

        // expect block A have a line with 3 changes (bundled)
        static::assertCount(3, $blockA->lines[1]->changes);
    }
}
