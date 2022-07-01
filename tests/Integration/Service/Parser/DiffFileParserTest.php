<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Integration\Service\Parser;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Service\Git\Diff\Bundle\DiffLineComparator;
use DR\GitCommitNotification\Service\Git\Diff\UnifiedDiffBundler;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedBlockParser;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedLineParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;

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
        static::assertCount(2, $fileDiff->blocks);

        // expect each block have 3 lines
        [$blockA, $blockB] = $fileDiff->blocks;
        static::assertCount(3, $blockA->lines);
        static::assertCount(3, $blockB->lines);

        // expect block A have a line with 3 changes (bundled)
        static::assertCount(3, $blockA->lines[1]->changes);
    }
}
