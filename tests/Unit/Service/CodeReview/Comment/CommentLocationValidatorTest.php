<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Service\CodeReview\Comment\CommentLocationValidator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[CoversClass(CommentLocationValidator::class)]
class CommentLocationValidatorTest extends AbstractTestCase
{
    private CodeReviewDiffService&MockObject $diffService;
    private CommentLocationValidator $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffService = $this->createMock(CodeReviewDiffService::class);
        $this->resolver    = new CommentLocationValidator($this->diffService);
    }

    public function testResolvesExactNewSidePathAndLine(): void
    {
        $review = new CodeReview();
        $file   = $this->createDiffFile('src/Foo.php', 'src/Foo.php', [
            $this->createLine(DiffLine::STATE_CHANGED, 4, 5),
        ]);

        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file]);

        $this->resolver->validate($review, 'src/Foo.php', 5);
    }

    public function testRejectsOldPathOfRenamedFile(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->diffService->expects($this->once())->method('getDiff')->willReturn([
            $this->createDiffFile('src/Old.php', 'src/New.php', [$this->createLine(DiffLine::STATE_CHANGED, 1, 1)]),
        ]);

        $this->resolver->validate(new CodeReview(), 'src/Old.php', 1);
    }

    public function testRejectsDeletedFile(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->diffService->expects($this->once())->method('getDiff')->willReturn([
            $this->createDiffFile('src/Foo.php', null, [$this->createLine(DiffLine::STATE_REMOVED, 1, null)]),
        ]);

        $this->resolver->validate(new CodeReview(), 'src/Foo.php', 1);
    }

    public function testRejectsRemovedOnlyLine(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->diffService->expects($this->once())->method('getDiff')->willReturn([
            $this->createDiffFile('src/Foo.php', 'src/Foo.php', [$this->createLine(DiffLine::STATE_REMOVED, 1, null)]),
        ]);

        $this->resolver->validate(new CodeReview(), 'src/Foo.php', 1);
    }

    public function testRejectsLineNotPresentInTheFileBlocks(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->diffService->expects($this->once())->method('getDiff')->willReturn([
            $this->createDiffFile('src/Foo.php', 'src/Foo.php', [$this->createLine(DiffLine::STATE_UNCHANGED, 1, 1)]),
        ]);

        $this->resolver->validate(new CodeReview(), 'src/Foo.php', 2);
    }

    /**
     * @param array<int, DiffLine> $lines
     */
    private function createDiffFile(string $oldPath, ?string $newPath, array $lines): DiffFile
    {
        $block        = new DiffBlock();
        $block->lines = $lines;
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
