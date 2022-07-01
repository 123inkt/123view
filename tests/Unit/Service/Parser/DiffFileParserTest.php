<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Parser;

use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\Unified\UnifiedBlockParser;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Parser\DiffFileParser
 * @covers ::__construct
 */
class DiffFileParserTest extends AbstractTestCase
{
    /** @var UnifiedBlockParser|MockObject */
    private UnifiedBlockParser $blockParser;
    private DiffFileParser     $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockParser = $this->createMock(UnifiedBlockParser::class);
        $this->parser      = new DiffFileParser($this->blockParser);
    }

    /**
     * @covers ::parse
     * @covers ::tryParse
     * @throws ParseException
     */
    public function testParseException(): void
    {
        // prepare data
        $contents = "new file mode 100644\n@@ -0,0 +1,16 @@\n";

        // prepare mocks
        $this->blockParser->expects(static::once())->method('parse')->willThrowException(new Exception('foobar'));

        $this->expectException(ParseException::class);
        $this->parser->parse($contents, new DiffFile());
    }

    /**
     * @covers ::parse
     * @covers ::tryParse
     * @covers ::readFileInfo
     * @throws ParseException
     */
    public function testParseNewFile(): void
    {
        // prepare data
        $contents = "new file mode 100644\n";
        $contents .= "index 0000000..ece0de9\n";
        $contents .= "--- /dev/null\n";
        $contents .= "+++ b/added-file.php\n";
        $contents .= "@@ -0,0 +1,16 @@\n";

        $file                 = new DiffFile();
        $file->filePathBefore = "before";
        $file->filePathAfter  = "after";
        $block                = new DiffBlock();

        // prepare mocks
        $this->blockParser->expects(static::once())->method('parse')->willReturn($block);

        $result = $this->parser->parse($contents, $file);
        static::assertNull($result->filePathBefore);
        static::assertSame('after', $result->filePathAfter);
        static::assertSame([$block], $result->blocks);
    }

    /**
     * @covers ::parse
     * @covers ::tryParse
     * @covers ::readFileInfo
     * @throws ParseException
     */
    public function testParseDeleteFile(): void
    {
        // prepare data
        $contents = "deleted file mode 100644\n";
        $contents .= "index a1a8ab7..0000000\n";
        $contents .= "--- a/test-delete-file.xml\n";
        $contents .= "+++ /dev/null\n";
        $contents .= "@@ -1,2 +0,0 @@\n";

        $file                 = new DiffFile();
        $file->filePathBefore = "before";
        $file->filePathAfter  = "after";
        $block                = new DiffBlock();

        // prepare mocks
        $this->blockParser->expects(static::once())->method('parse')->willReturn($block);

        $result = $this->parser->parse($contents, $file);
        static::assertSame('before', $result->filePathBefore);
        static::assertNull($result->filePathAfter);
        static::assertSame([$block], $result->blocks);
    }

    /**
     * @covers ::parse
     * @covers ::tryParse
     * @covers ::readFileInfo
     * @throws ParseException
     */
    public function testParseChanges(): void
    {
        // prepare data
        $contents = "index fb42e28..db43761 100644\n";
        $contents .= "--- a/test-change-file.xml\n";
        $contents .= "+++ b/test-change-file.xml\n";
        $contents .= "@@ -29,8 +29,8 @@\n";

        $file                 = new DiffFile();
        $file->filePathBefore = "before";
        $file->filePathAfter  = "after";
        $block                = new DiffBlock();

        // prepare mocks
        $this->blockParser->expects(static::once())->method('parse')->willReturn($block);

        $result = $this->parser->parse($contents, $file);
        static::assertSame('before', $result->filePathBefore);
        static::assertSame('after', $result->filePathAfter);
        static::assertSame([$block], $result->blocks);
    }

    /**
     * @covers ::parse
     * @covers ::tryParse
     * @covers ::readFileInfo
     * @throws ParseException
     */
    public function testParseMultiBlockChanges(): void
    {
        // prepare data
        $contents = "index fb42e28..db43761 100644\n";
        $contents .= "--- a/test-change-file.xml\n";
        $contents .= "+++ b/test-change-file.xml\n";
        $contents .= "@@ -29,8 +30,8 @@\n";
        $contents .= "+change1\n";
        $contents .= "@@ -60,4 +71,4 @@\n";
        $contents .= "+change2\n";

        $file                 = new DiffFile();
        $file->filePathBefore = "before";
        $file->filePathAfter  = "after";
        $blockA               = new DiffBlock();
        $blockB               = new DiffBlock();

        // prepare mocks
        $this->blockParser->expects(static::exactly(2))->method('parse')
            ->withConsecutive([29, 30], [60, 71])
            ->willReturn($blockA, $blockB);

        $result = $this->parser->parse($contents, $file);
        static::assertSame('before', $result->filePathBefore);
        static::assertSame('after', $result->filePathAfter);
        static::assertSame([$blockA, $blockB], $result->blocks);
    }
}
