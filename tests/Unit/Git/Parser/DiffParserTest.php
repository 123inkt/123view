<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Git\Parser;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Parser\DiffParser
 * @covers ::__construct
 */
class DiffParserTest extends AbstractTest
{
    private DiffParser $parser;
    /** @var DiffFileParser|MockObject */
    private DiffFileParser $fileParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileParser = $this->createMock(DiffFileParser::class);
        $this->parser     = new DiffParser($this->log, $this->fileParser);
    }

    /**
     * @covers ::parse
     */
    public function testParseDeletionsOnly(): void
    {
        $diffs = $this->parser->parse('test');
        static::assertCount(0, $diffs);
    }

    /**
     * @covers ::parse
     */
    public function testParseSingleFile(): void
    {
        $input = "\n";
        $input .= "diff --git a/example.txt b/example.txt\n";
        $input .= "foobar\n";

        $file = new DiffFile();

        // setup mocks
        $this->fileParser->expects(static::once())->method('parse')->with("foobar\n")->willReturn($file);

        $diffs = $this->parser->parse($input);
        static::assertSame([$file], $diffs);
    }

    /**
     * @covers ::parse
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
        $this->fileParser->expects(static::exactly(2))->method('parse')
            ->withConsecutive(["foobar A"], ["foobar B\n"])
            ->willReturn($fileA, $fileB);

        $diffs = $this->parser->parse($input);
        static::assertSame([$fileA, $fileB], $diffs);
    }
}
