<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentVisibility;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibilityProvider;
use DR\Review\Service\CodeReview\DiffComparePolicyProvider;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\CommentsViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\CommentsViewModelProvider
 * @covers ::__construct
 */
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

    /**
     * @covers ::getCommentsViewModel
     */
    public function testGetCommentsViewModel(): void
    {
        $commentA = new Comment();
        $commentA->setLineReference(new LineReference('comment-1', 1, 2, 3));
        $commentB = new Comment();
        $commentB->setLineReference(new LineReference('comment-2', 4, 5, 6));
        $comments = [$commentA, $commentB];
        $review   = new CodeReview();
        $file     = new DiffFile();
        $line     = new DiffLine(0, []);

        $file->filePathBefore = '/path/to/fileBefore';
        $file->filePathAfter  = '/path/to/fileAfter';

        $this->commentRepository->expects(self::once())
            ->method('findByReview')
            ->with($review, ['/path/to/fileAfter', '/path/to/fileBefore'])
            ->willReturn($comments);
        $this->diffFinder->expects(self::exactly(2))
            ->method('findLineInFile')
            ->will(static::onConsecutiveCalls([$file, $commentA->getLineReference()], [$file, $commentB->getLineReference()]))
            ->willReturn($line, null);
        $this->comparePolicyProvider->expects(self::once())->method('getComparePolicy')->willReturn(DiffComparePolicy::IGNORE);
        $this->visibilityProvider->expects(self::once())->method('getCommentVisibility')->willReturn(CommentVisibility::NONE);

        $viewModel = $this->provider->getCommentsViewModel($review, $file);
        static::assertSame([$commentA], $viewModel->getComments($line));
        static::assertSame([$commentB], $viewModel->detachedComments);
        static::assertSame(DiffComparePolicy::IGNORE, $viewModel->comparisonPolicy);
        static::assertSame(CommentVisibility::NONE, $viewModel->commentVisibility);
    }
}
