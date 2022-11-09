<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

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
        static::assertSame($lineC, $this->finder->findLineInLines($lines, new LineReference('', 101, 1, 102)));
        static::assertNull($this->finder->findLineInLines($lines, new LineReference('', 103, 0, 103)));
    }

    /**
     * @covers ::findLineInFile
     */
    public function testFindLineInFile(): void
    {
    }

    /**
     * @covers ::findLineInBlock
     */
    public function testFineLineInBlock(): void
    {
    }

    /**
     * @covers ::findLineInNewFile
     */
    public function testFindLineInNewFile(): void
    {
    }
}
