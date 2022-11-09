<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
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
    }

    /**
     * @covers ::findLineInFile
     */
    public function testFindLineInFile(): void
    {
    }

    /**
     * @covers ::fineLineInBlock
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
