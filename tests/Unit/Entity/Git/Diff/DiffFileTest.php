<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffFile::class)]
class DiffFileTest extends AbstractTestCase
{
    public function testGetBlocks(): void
    {
        $file = new DiffFile();
        static::assertCount(0, $file->getBlocks());

        $block = new DiffBlock();
        $file->addBlock($block);
        static::assertSame([$block], $file->getBlocks());

        $file->removeBlocks();
        static::assertCount(0, $file->getBlocks());
    }

    public function testAddBlocks(): void
    {
        $file = new DiffFile();
        static::assertCount(0, $file->getBlocks());

        $block = new DiffBlock();
        $file->addBlocks([$block]);
        static::assertSame([$block], $file->getBlocks());
    }

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

    public function testGetFileBefore(): void
    {
        $file = new DiffFile();
        static::assertNull($file->getFile());

        $file->filePathBefore = '/foo/before.txt';
        static::assertSame('/foo/before.txt', $file->getFile()?->getPathname());
    }

    public function testGetFileAfter(): void
    {
        $file = new DiffFile();
        static::assertNull($file->getFile());

        $file->filePathBefore = '/foo/before.txt';
        $file->filePathAfter = '/foo/after.txt';
        static::assertSame('/foo/after.txt', $file->getFile()?->getPathname());
    }

    public function testGetFilename(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getFilename());

        $file->filePathBefore = 'before.txt';
        static::assertSame('before.txt', $file->getFilename());

        $file->filePathAfter = 'after.txt';
        static::assertSame('after.txt', $file->getFilename());
    }

    public function testGetExtension(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getExtension());

        $file->filePathBefore = 'before.txt';
        static::assertSame('txt', $file->getExtension());

        $file->filePathAfter = 'after.xls';
        static::assertSame('xls', $file->getExtension());
    }

    public function testGetPathname(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getPathname());

        $file->filePathBefore = '/before/before.txt';
        static::assertSame('/before/before.txt', $file->getPathname());

        $file->filePathAfter = '/after/after.txt';
        static::assertSame('/after/after.txt', $file->getPathname());
    }

    public function testGetDirname(): void
    {
        $file = new DiffFile();
        static::assertSame('', $file->getDirname());

        $file->filePathBefore = '/before/before.txt';
        static::assertSame('/before', $file->getDirname());

        $file->filePathAfter = '/after/after.txt';
        static::assertSame('/after', $file->getDirname());
    }

    public function testIsModified(): void
    {
        $file = new DiffFile();
        static::assertFalse($file->isModified());

        $file->filePathBefore = 'before.txt';
        static::assertFalse($file->isModified());

        $file->filePathAfter = 'after.txt';
        static::assertTrue($file->isModified());
    }

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

    public function testGetMimeType(): void
    {
        $file                 = new DiffFile();
        $file->filePathBefore = 'foobar.txt';
        static::assertSame('text/plain', $file->getMimeType());

        $file->filePathAfter = 'foobar.jpg';
        static::assertSame('image/jpeg', $file->getMimeType());
    }

    public function testIsImage(): void
    {
        $file                 = new DiffFile();
        $file->filePathBefore = 'foobar.txt';
        static::assertFalse($file->isImage());

        $file->filePathAfter = 'foobar.jpg';
        static::assertTrue($file->isImage());
    }

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

        $file = new DiffFile();
        $file->addBlock($block);
        static::assertSame(4, $file->getTotalNrOfLines());
    }

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
