<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LineReferenceFactory::class)]
class LineReferenceFactoryTest extends AbstractTestCase
{
    private MockObject&CodeReviewDiffService $diffService;
    private MockObject&DiffFinder            $diffFinder;
    private LineReferenceFactory             $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(CodeReviewDiffService::class);
        $this->diffFinder  = $this->createMock(DiffFinder::class);
        $this->factory     = new LineReferenceFactory($this->diffService, $this->diffFinder);
    }

    public function testCreateFromReviewFileNotInDiff(): void
    {
        $review    = new CodeReview();
        $diffFiles = [];

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn($diffFiles);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->with($diffFiles, 'src/Foo.php')->willReturn(null);

        $ref = $this->factory->createFromReview($review, 'src/Foo.php', 10, 'abc123');

        static::assertSame('src/Foo.php', $ref->oldPath);
        static::assertSame('src/Foo.php', $ref->newPath);
        static::assertSame(10, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(10, $ref->lineAfter);
        static::assertSame('abc123', $ref->headSha);
    }

    public function testCreateFromReviewFileFound(): void
    {
        $review   = new CodeReview();
        $diffFile = $this->createDiffFile('src/Foo.php', 'src/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 5, 5),
            $this->createLine(DiffLine::STATE_UNCHANGED, 6, 6),
        ]);

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$diffFile]);
        $this->diffFinder->expects($this->once())->method('findFileByPath')->willReturn($diffFile);

        $ref = $this->factory->createFromReview($review, 'src/Foo.php', 6, 'abc123');

        static::assertSame(6, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(6, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Unmodified, $ref->state);
    }

    public function testCreateFromDiffFileUnchangedLine(): void
    {
        $diffFile = $this->createDiffFile('old/Foo.php', 'new/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 3, 3),
            $this->createLine(DiffLine::STATE_UNCHANGED, 4, 4),
            $this->createLine(DiffLine::STATE_UNCHANGED, 5, 5),
        ]);

        $this->diffService->expects($this->never())->method(static::anything());
        $this->diffFinder->expects($this->never())->method(static::anything());
        $ref = $this->factory->createFromDiffFile($diffFile, 4, 'abc');

        static::assertSame('old/Foo.php', $ref->oldPath);
        static::assertSame('new/Foo.php', $ref->newPath);
        static::assertSame(4, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(4, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Unmodified, $ref->state);
    }

    public function testCreateFromDiffFileAddedLine(): void
    {
        // Old: 1,2,3  New: 1,2,new_a(3),new_b(4),3→5
        $diffFile = $this->createDiffFile('old/Foo.php', 'new/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 1, 1),
            $this->createLine(DiffLine::STATE_UNCHANGED, 2, 2),
            $this->createLine(DiffLine::STATE_ADDED, null, 3),
            $this->createLine(DiffLine::STATE_ADDED, null, 4),
            $this->createLine(DiffLine::STATE_UNCHANGED, 3, 5),
        ]);

        $this->diffService->expects($this->never())->method(static::anything());
        $this->diffFinder->expects($this->never())->method(static::anything());

        // Target: second added line (lineAfter=4); anchor is lineNumberBefore=2, offset=2
        $ref = $this->factory->createFromDiffFile($diffFile, 4, 'sha1');

        static::assertSame(2, $ref->line);
        static::assertSame(2, $ref->offset);
        static::assertSame(4, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Added, $ref->state);
        // invariant: line + offset = lineAfter
        static::assertSame($ref->lineAfter, $ref->line + $ref->offset);
    }

    public function testCreateFromDiffFileModifiedLine(): void
    {
        $diffFile = $this->createDiffFile('old/Foo.php', 'new/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 9, 9),
            $this->createLine(DiffLine::STATE_CHANGED, 10, 10),
        ]);

        $this->diffService->expects($this->never())->method(static::anything());
        $this->diffFinder->expects($this->never())->method(static::anything());
        $ref = $this->factory->createFromDiffFile($diffFile, 10, 'sha2');

        static::assertSame(10, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(10, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Modified, $ref->state);
    }

    public function testCreateFromDiffFileShiftedLine(): void
    {
        // Two lines added at the top shift old line 3 to new line 5
        $diffFile = $this->createDiffFile('old/Foo.php', 'new/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 1, 1),
            $this->createLine(DiffLine::STATE_ADDED, null, 2),
            $this->createLine(DiffLine::STATE_ADDED, null, 3),
            $this->createLine(DiffLine::STATE_UNCHANGED, 2, 4),
            $this->createLine(DiffLine::STATE_UNCHANGED, 3, 5),
        ]);

        $this->diffService->expects($this->never())->method(static::anything());
        $this->diffFinder->expects($this->never())->method(static::anything());
        $ref = $this->factory->createFromDiffFile($diffFile, 5, 'sha3');

        static::assertSame(3, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(5, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Unmodified, $ref->state);
    }

    public function testCreateFromDiffFileLineNotFound(): void
    {
        $diffFile = $this->createDiffFile('old/Foo.php', 'new/Foo.php', [
            $this->createLine(DiffLine::STATE_UNCHANGED, 1, 1),
        ]);

        $this->diffService->expects($this->never())->method(static::anything());
        $this->diffFinder->expects($this->never())->method(static::anything());

        // Request line 99 which is not in the diff
        $ref = $this->factory->createFromDiffFile($diffFile, 99, 'sha4');

        static::assertSame(99, $ref->line);
        static::assertSame(0, $ref->offset);
        static::assertSame(99, $ref->lineAfter);
        static::assertSame(LineReferenceStateEnum::Unknown, $ref->state);
    }

    /**
     * @param array<int, DiffLine> $lines
     */
    private function createDiffFile(string $oldPath, string $newPath, array $lines): DiffFile
    {
        $block                = new DiffBlock();
        $block->lines         = $lines;
        $file                 = new DiffFile();
        $file->filePathBefore = $oldPath;
        $file->filePathAfter  = $newPath;
        $file->addBlock($block);

        return $file;
    }

    private function createLine(int $state, ?int $lineNumberBefore, ?int $lineNumberAfter): DiffLine
    {
        $line                   = new DiffLine($state, []);
        $line->lineNumberBefore = $lineNumberBefore;
        $line->lineNumberAfter  = $lineNumberAfter;

        return $line;
    }
}
