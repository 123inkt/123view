<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use DR\Review\Service\Parser\DiffFileParser;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Review\Service\Parser\Unified\UnifiedLineParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DiffFileParserTest extends AbstractTestCase
{
    private DiffFileParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $blockParser  = new UnifiedBlockParser(new UnifiedLineParser());
        $this->parser = new DiffFileParser($blockParser);
    }

    /**
     * @throws ParseException
     */
    public function testParse(): void
    {
        $contents                 = $this->getFileContents('deletions-and-additions.txt');
        $fileDiff                 = new DiffFile();
        $fileDiff->filePathBefore = 'example.yml';
        $fileDiff->filePathAfter  = 'example.yml';

        // expect 2 blocks
        $fileDiff = $this->parser->parse($contents, $fileDiff);
        static::assertCount(2, $fileDiff->getBlocks());

        // expect first block to have 4 lines, second block 3 lines
        [$blockA, $blockB] = $fileDiff->getBlocks();
        static::assertCount(4, $blockA->lines);
        static::assertCount(3, $blockB->lines);
    }
}
