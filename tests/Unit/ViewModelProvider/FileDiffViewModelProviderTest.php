<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Model\Review\Action\AddCommentAction;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\CommentViewModelProvider;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\FileDiffViewModelProvider
 * @covers ::__construct
 */
class FileDiffViewModelProviderTest extends AbstractTestCase
{
    private CommentViewModelProvider&MockObject        $commentModelProvider;
    private CacheableHighlightedFileService&MockObject $highlightedFileService;
    private UnifiedDiffBundler&MockObject              $bundler;
    private FileDiffViewModelProvider                  $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentModelProvider   = $this->createMock(CommentViewModelProvider::class);
        $this->highlightedFileService = $this->createMock(CacheableHighlightedFileService::class);
        $this->bundler                = $this->createMock(UnifiedDiffBundler::class);
        $this->provider               = new FileDiffViewModelProvider($this->commentModelProvider, $this->highlightedFileService, $this->bundler);
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelAddComment(): void
    {
        $action              = new AddCommentAction(new LineReference('reference', 1, 2, 3));
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getAddCommentViewModel')->with($review, $file, $action);
        $this->bundler->expects(self::once())->method('bundleFile')->with($file);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, ReviewDiffModeEnum::INLINE);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelEditComment(): void
    {
        $action              = new EditCommentAction(new Comment());
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getEditCommentViewModel')->with($action);
        $this->bundler->expects(self::never())->method('bundleFile');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, ReviewDiffModeEnum::UNIFIED);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelAddCommentReply(): void
    {
        $action              = new AddCommentReplyAction(new Comment());
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getReplyCommentViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, ReviewDiffModeEnum::INLINE);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelEditCommentReply(): void
    {
        $action              = new EditCommentReplyAction(new CommentReply());
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getEditCommentReplyViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, ReviewDiffModeEnum::INLINE);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelNoHighlightIfFileIsDeleted(): void
    {
        $file                 = new DiffFile();
        $file->filePathBefore = 'filepath';
        $review               = new CodeReview();

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::never())->method('fromDiffFile');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, ReviewDiffModeEnum::INLINE);
        static::assertNull($viewModel->getHighlightedFile());
    }
}
