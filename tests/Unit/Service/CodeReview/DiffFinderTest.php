<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffBlock;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Git\Diff\DiffLine;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\DiffFinder
 */
class DiffFinderTest extends AbstractTestCase
{
    private DiffFinder $finder;

    public function setUp(): void
    {
        parent::setUp();
        $this->finder = new DiffFinder();
    }

    /**
     * @covers ::findFileByPath
     */
    public function testFindFileByPath(): void
    {
        $fileA                 = new DiffFile();
        $fileA->filePathBefore = '/path/to/file/deleted.txt';

        $fileB                 = new DiffFile();
        $fileB->filePathBefore = '/path/to/file/changed.txt';
        $fileB->filePathAfter  = '/path/to/file/changed.doc';

        $fileC                = new DiffFile();
        $fileC->filePathAfter = '/path/to/file/created.txt';

        $files = [$fileA, $fileB, $fileC];

        static::assertSame($fileA, $this->finder->findFileByPath($files, '/path/to/file/deleted.txt'));
        static::assertNull($this->finder->findFileByPath($files, '/path/to/file/changed.txt'));
        static::assertSame($fileB, $this->finder->findFileByPath($files, '/path/to/file/changed.doc'));
        static::assertSame($fileC, $this->finder->findFileByPath($files, '/path/to/file/created.txt'));

        // with hash
        static::assertNull($this->finder->findFileByPath($files, '/path/to/file/created.txt:foobar'));
        $fileC->hashEnd = 'foobar';
        static::assertSame($fileC, $this->finder->findFileByPath($files, '/path/to/file/created.txt:foobar'));
    }

    /**
     * @covers ::findLinesAround
     */
    public function testFindLinesAround(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 100;

        $lineB                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB->lineNumberBefore = 101;
        $lineB->lineNumberAfter  = 101;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = 102;
        $lineC->lineNumberAfter  = 102;

        $lineD                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD->lineNumberBefore = 103;
        $lineD->lineNumberAfter  = 103;

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC, $lineD];

        $file                 = new DiffFile();
        $file->filePathBefore = '/path/to/file/foobar.txt';
        $file->filePathAfter  = '/path/to/file/foobar.txt';
        $file->addBlock($block);

        // match line 100 => expect before: 100, after: 101
        static::assertSame(
            ['before' => [$lineA], 'after' => [$lineB]],
            $this->finder->findLinesAround($file, new LineReference('', 100, 0, 100), 1)
        );

        // match line 101 => expect before:  101, after: 102
        static::assertSame(
            ['before' => [$lineB], 'after' => [$lineC]],
            $this->finder->findLinesAround($file, new LineReference('', 101, 0, 101), 1)
        );

        // match line 101 => expect before:  101, after: 102, margin 2
        static::assertSame(
            ['before' => [$lineA, $lineB], 'after' => [$lineC, $lineD]],
            $this->finder->findLinesAround($file, new LineReference('', 101, 0, 101), 2)
        );

        static::assertNull($this->finder->findLinesAround($file, new LineReference('', 105, 0, 105), 1));
    }

    /**
     * @covers ::findLineInLines
     */
    public function testFindLineInLines(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 100;

        $lineB                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB->lineNumberAfter = 101;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = 101;
        $lineC->lineNumberAfter  = 102;

        $lineD                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineD->lineNumberBefore = 102;
        $lineD->lineNumberAfter  = 103;

        $lines = [$lineA, $lineB, $lineC, $lineD];

        static::assertSame($lineA, $this->finder->findLineInLines($lines, new LineReference('', 100, 0, 100)));
        static::assertSame($lineB, $this->finder->findLineInLines($lines, new LineReference('', 100, 1, 101)));
        static::assertSame($lineA, $this->finder->findLineInLines($lines, new LineReference('', 100, 4, 100)));
        static::assertSame($lineC, $this->finder->findLineInLines($lines, new LineReference('', 101, 1, 102)));
        static::assertNull($this->finder->findLineInLines($lines, new LineReference('', 103, 0, 103)));
    }

    /**
     * @covers ::findLineInFile
     */
    public function testFindLineInFile(): void
    {
        $line                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $line->lineNumberAfter = 100;

        $block        = new DiffBlock();
        $block->lines = [99 => $line];

        $file = new DiffFile();
        $file->addBlock($block);
        $file->filePathAfter = '/path/to/file/foobar.txt';

        static::assertNull($this->finder->findLineInFile($file, new LineReference('', 99, 0, 99)));
        static::assertSame($line, $this->finder->findLineInFile($file, new LineReference('', 100, 0, 100)));
    }

    /**
     * @covers ::findLineInBlock
     */
    public function testFineLineInBlockForNewFile(): void
    {
        $line                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $line->lineNumberAfter = 100;

        $block        = new DiffBlock();
        $block->lines = [99 => $line];

        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/file/foobar.txt';

        static::assertNull($this->finder->findLineInBlock($file, $block, new LineReference('', 99, 0, 99)));
        static::assertSame($line, $this->finder->findLineInBlock($file, $block, new LineReference('', 100, 0, 100)));
    }

    /**
     * @covers ::findLineInBlock
     */
    public function testFineLineInBlockForLines(): void
    {
        $line                   = new DiffLine(DiffLine::STATE_ADDED, []);
        $line->lineNumberBefore = 100;
        $line->lineNumberAfter  = 100;

        $block        = new DiffBlock();
        $block->lines = [99 => $line];

        $file                 = new DiffFile();
        $file->filePathBefore = '/path/to/file/foobar.txt';
        $file->filePathAfter  = '/path/to/file/foobar.txt';

        static::assertNull($this->finder->findLineInBlock($file, $block, new LineReference('', 99, 0, 99)));
        static::assertSame($line, $this->finder->findLineInBlock($file, $block, new LineReference('', 100, 0, 100)));
    }

    /**
     * @covers ::findLineInNewFile
     */
    public function testFindLineInNewFile(): void
    {
        $lineA                  = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberAfter = 100;

        $lineB                  = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineB->lineNumberAfter = 101;

        $lines = [99 => $lineA, 100 => $lineB];

        static::assertNull($this->finder->findLineInNewFile($lines, new LineReference('', 99, 0, 99)));
        static::assertSame($lineA, $this->finder->findLineInNewFile($lines, new LineReference('', 100, 0, 100)));
        static::assertSame($lineB, $this->finder->findLineInNewFile($lines, new LineReference('', 101, 0, 101)));
    }
}
