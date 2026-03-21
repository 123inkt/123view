<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use DR\Review\Service\Parser\DiffFileParser;
use DR\Review\Service\Parser\DiffParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(DiffParser::class)]
class DiffParserTest extends AbstractTestCase
{
    private DiffParser                $parser;
    private DiffFileParser&MockObject $fileParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileParser = $this->createMock(DiffFileParser::class);
        $this->parser     = new DiffParser($this->fileParser);
    }

    /**
     * @throws ParseException
     */
    public function testParseDeletionsOnly(): void
    {
        $this->fileParser->expects($this->never())->method('parse');
        $diffs = $this->parser->parse('test', true);
        static::assertCount(0, $diffs);
    }

    /**
     * @throws ParseException
     */
    public function testParseSingleFile(): void
    {
        $input = "\n";
        $input .= "diff --git a/example.txt b/example.txt\n";
        $input .= "foobar\n";

        $file = new DiffFile();

        // setup mocks
        $this->fileParser->expects($this->once())->method('parse')->with("foobar\n")->willReturn($file);

        $diffs = $this->parser->parse($input, false);
        static::assertSame([$file], $diffs);
    }

    /**
     * @throws ParseException
     */
    public function testParseSingleFileWithNew(): void
    {
        $input = "\n";
        $input .= "diff --git a/example with space/exampleA.txt b/example with space/exampleB.txt\n";
        $input .= "foobar\n";

        // setup mocks
        $this->fileParser->expects($this->once())->method('parse')->with("foobar\n")->willReturnArgument(1);

        $diffs = $this->parser->parse($input, true);
        static::assertCount(1, $diffs);
        $file = $diffs[0];
        static::assertSame('example with space/exampleA.txt', $file->filePathBefore);
        static::assertSame('example with space/exampleB.txt', $file->filePathAfter);
    }

    /**
     * @throws ParseException
     */
    public function testParseTwoFiles(): void
    {
        $input = "\n";
        $input .= "diff --git a/example.txt b/example.txt\n";
        $input .= "foobar A\n";
        $input .= "diff --git a/example.git b/example.git\n";
        $input .= "foobar B\n";

        $fileA = new DiffFile();
        $fileB = new DiffFile();

        // setup mocks
        $this->fileParser->expects($this->exactly(2))->method('parse')
            ->with(...consecutive(["foobar A"], ["foobar B\n"]))
            ->willReturn($fileA, $fileB);

        $diffs = $this->parser->parse($input, false);
        static::assertSame([$fileA, $fileB], $diffs);
    }
}
