<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\Diff\DiffFile
 */
class DiffFileTest extends AbstractTestCase
{
    /**
     * @covers ::getBlocks
     * @covers ::addBlock
     */
    public function testGetBlocks(): void
    {
        $file = new DiffFile();
        static::assertCount(0, $file->getBlocks());

        $block = new DiffBlock();
        $file->addBlock($block);
        static::assertSame([$block], $file->getBlocks());
    }

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
     * @covers ::getFile
     */
    public function testGetFile(): void
    {
        $file = new DiffFile();
        static::assertNull($file->getFile());

        $file->filePathBefore = '/foo/before.txt';
        static::assertSame('/foo/before.txt', $file->getFile()?->getPathname());

        $file->filePathAfter = '/foo/after.txt';
        static::assertSame('/foo/after.txt', $file->getFile()?->getPathname());
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
     * @covers ::getPathname
     */
    public function testGetPathname(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getPathname());

        $file->filePathBefore = '/before/before.txt';
        static::assertSame('/before/before.txt', $file->getPathname());

        $file->filePathAfter = '/after/after.txt';
        static::assertSame('/after/after.txt', $file->getPathname());
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

    /**
     * @covers ::getLines
     */
    public function testGetLines(): void
    {
        $lineA        = new DiffLine(DiffLine::STATE_REMOVED, [new DiffChange(DiffChange::REMOVED, 'line 1')]);
        $lineB        = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'line 2')]);
        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB];
        $file         = new DiffFile();
        $file->addBlock($block);

        static::assertSame(['line 2'], $file->getLines());
    }

    /**
     * @covers ::getNrOfLinesAdded
     * @covers ::getNrOfLinesRemoved
     * @covers ::updateLinesChanged
     */
    public function testGetNrOfLinesAdded(): void
    {
        // 2 lines added, 1 changed, 1 removed, and 1 unchanged
        $lineAddedA    = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineAddedB    = new DiffLine(DiffLine::STATE_ADDED, []);
        $lineChanged   = new DiffLine(DiffLine::STATE_CHANGED, []);
        $lineRemoved   = new DiffLine(DiffLine::STATE_REMOVED, []);
        $lineUnchanged = new DiffLine(DiffLine::STATE_UNCHANGED, []);

        $block        = new DiffBlock();
        $block->lines = [$lineAddedA, $lineAddedB, $lineChanged, $lineRemoved, $lineUnchanged];

        $file = new DiffFile();
        $file->addBlock($block);
        static::assertSame(3, $file->getNrOfLinesAdded());

        $file = new DiffFile();
        $file->addBlock($block);
        static::assertSame(2, $file->getNrOfLinesRemoved());
    }

    /**
     * @covers ::getMaxLineNumberLength
     */
    public function testGetMaxLineNumberLength(): void
    {
        $lineA                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineA->lineNumberBefore = 100;
        $lineA->lineNumberAfter  = 200;

        $lineB                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineB->lineNumberBefore = 1000;
        $lineB->lineNumberAfter  = null;

        $lineC                   = new DiffLine(DiffLine::STATE_UNCHANGED, []);
        $lineC->lineNumberBefore = null;
        $lineC->lineNumberAfter  = 20000;

        $block        = new DiffBlock();
        $block->lines = [$lineA, $lineB, $lineC];

        $file = new DiffFile();
        $file->addBlock($block);

        static::assertSame(4, $file->getMaxLineNumberLength(true));
        static::assertSame(5, $file->getMaxLineNumberLength(false));
    }
}
