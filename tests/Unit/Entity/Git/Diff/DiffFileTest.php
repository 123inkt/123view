<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git\Diff;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Diff\DiffFile
 */
class DiffFileTest extends AbstractTestCase
{
    /**
     * @covers ::getFileMode
     */
    public function testGetFileMode(): void
    {
        $file = new DiffFile();

        $file->filePathBefore = 'foobar';
        static::assertSame(DiffFile::FILE_DELETED, $file->getFileMode());

        $file->filePathAfter = 'foobar';
        static::assertSame(DiffFile::FILE_MODIFIED, $file->getFileMode());

        $file->filePathBefore = null;
        static::assertSame(DiffFile::FILE_ADDED, $file->getFileMode());
    }

    /**
     * @covers ::getFilename
     */
    public function testGetFilename(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getFilename());

        $file->filePathBefore = 'before.txt';
        static::assertSame('before.txt', $file->getFilename());

        $file->filePathAfter = 'after.txt';
        static::assertSame('after.txt', $file->getFilename());
    }

    /**
     * @covers ::getExtension
     */
    public function testGetExtension(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getExtension());

        $file->filePathBefore = 'before.txt';
        static::assertSame('txt', $file->getExtension());

        $file->filePathAfter = 'after.xls';
        static::assertSame('xls', $file->getExtension());
    }

    /**
     * @covers ::getDirname
     */
    public function testGetDirname(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getDirname());

        $file->filePathBefore = '/before/before.txt';
        static::assertSame('/before', $file->getDirname());

        $file->filePathAfter = '/after/after.txt';
        static::assertSame('/after', $file->getDirname());
    }

    /**
     * @covers ::isModified
     */
    public function testIsModified(): void
    {
        $file = new DiffFile();
        static::assertFalse($file->isModified());

        $file->filePathBefore = 'before.txt';
        static::assertFalse($file->isModified());

        $file->filePathAfter = 'after.txt';
        static::assertTrue($file->isModified());
    }

    /**
     * @covers ::isDeleted
     */
    public function testIsDeleted(): void
    {
        $file = new DiffFile();
        static::assertTrue($file->isDeleted());

        $file->filePathBefore = 'before.txt';
        $file->filePathAfter  = null;
        static::assertTrue($file->isDeleted());

        $file->filePathBefore = null;
        $file->filePathAfter  = 'after.txt';
        static::assertFalse($file->isDeleted());
    }

    /**
     * @covers ::isAdded
     */
    public function testIsAdded(): void
    {
        $file = new DiffFile();
        static::assertTrue($file->isAdded());

        $file->filePathBefore = null;
        $file->filePathAfter  = 'after.txt';
        static::assertTrue($file->isAdded());

        $file->filePathBefore = 'before.txt';
        $file->filePathAfter  = null;
        static::assertFalse($file->isAdded());
    }

    /**
     * @covers ::isRename
     */
    public function testIsRename(): void
    {
        $file = new DiffFile();
        static::assertFalse($file->isRename());

        $file->filePathBefore = 'same-filename';
        $file->filePathAfter  = 'same-filename';
        static::assertFalse($file->isRename());

        $file->filePathBefore = 'different';
        $file->filePathAfter  = 'filename';
        static::assertTrue($file->isRename());
    }
}
