<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibilityProvider;
use DR\Review\Service\CodeReview\DiffComparePolicyProvider;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\CommentsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(CommentsViewModelProvider::class)]
class CommentsViewModelProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject         $commentRepository;
    private DiffFinder&MockObject                $diffFinder;
    private DiffComparePolicyProvider&MockObject $comparePolicyProvider;
    private CommentVisibilityProvider&MockObject $visibilityProvider;
    private CommentsViewModelProvider            $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->diffFinder            = $this->createMock(DiffFinder::class);
        $this->comparePolicyProvider = $this->createMock(DiffComparePolicyProvider::class);
        $this->visibilityProvider    = $this->createMock(CommentVisibilityProvider::class);
        $this->provider              = new CommentsViewModelProvider(
            $this->commentRepository,
            $this->diffFinder,
            $this->comparePolicyProvider,
            $this->visibilityProvider
        );
    }

    public function testGetCommentsViewModel(): void
    {
        $commentA = new Comment();
        $commentA->setLineReference(new LineReference(null, 'comment-1', 1, 2, 3));
        $commentB = new Comment();
        $commentB->setLineReference(new LineReference(null, 'comment-2', 4, 0, 0));
        $comments   = [$commentA, $commentB];
        $review     = new CodeReview();
        $file       = new DiffFile();
        $fileBefore = new DiffFile();
        $line       = new DiffLine(0, []);

        $file->filePathBefore       = '/path/to/fileBefore';
        $file->filePathAfter        = '/path/to/fileAfter';
        $fileBefore->filePathBefore = 'fileBefore';

        $this->commentRepository->expects($this->once())
            ->method('findByReview')
            ->with($review, ['/path/to/fileAfter', '/path/to/fileBefore'])
            ->willReturn($comments);
        $this->diffFinder->expects($this->exactly(2))
            ->method('findLineInFile')
            ->with(...consecutive([$file, $commentA->getLineReference()], [$fileBefore, $commentB->getLineReference()]))
            ->willReturn($line, null);
        $this->comparePolicyProvider->expects($this->once())->method('getComparePolicy')->willReturn(DiffComparePolicy::IGNORE);
        $this->visibilityProvider->expects($this->once())->method('getCommentVisibility')->willReturn(CommentVisibilityEnum::NONE);

        $viewModel = $this->provider->getCommentsViewModel($review, $fileBefore, $file);
        static::assertSame([$commentA], $viewModel->getComments($line));
        static::assertSame([$commentB], $viewModel->detachedComments);
        static::assertSame(DiffComparePolicy::IGNORE, $viewModel->comparisonPolicy);
        static::assertSame(CommentVisibilityEnum::NONE, $viewModel->commentVisibility);
    }
}
