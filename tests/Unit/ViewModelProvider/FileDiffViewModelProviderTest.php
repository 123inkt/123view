<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Service\Git\Diff\UnifiedDiffSplitter;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\CommentsViewModelProvider;
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
    private CommentsViewModelProvider&MockObject       $commentsModelProvider;
    private CacheableHighlightedFileService&MockObject $highlightedFileService;
    private UnifiedDiffBundler&MockObject              $bundler;
    private UnifiedDiffEmphasizer&MockObject           $emphasizer;
    private UnifiedDiffSplitter&MockObject             $splitter;
    private FileDiffViewModelProvider                  $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentModelProvider   = $this->createMock(CommentViewModelProvider::class);
        $this->commentsModelProvider  = $this->createMock(CommentsViewModelProvider::class);
        $this->highlightedFileService = $this->createMock(CacheableHighlightedFileService::class);
        $this->bundler                = $this->createMock(UnifiedDiffBundler::class);
        $this->emphasizer             = $this->createMock(UnifiedDiffEmphasizer::class);
        $this->splitter               = $this->createMock(UnifiedDiffSplitter::class);
        $this->provider               = new FileDiffViewModelProvider(
            $this->commentModelProvider,
            $this->commentsModelProvider,
            $this->highlightedFileService,
            $this->bundler,
            $this->emphasizer,
            $this->splitter
        );
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelInline(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', static fn() => []);

        $this->commentsModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects(self::once())->method('bundleFile')->with($file);
        $this->emphasizer->expects(self::never())->method('emphasizeFile');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelUnified(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', static fn() => []);

        $this->commentsModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects(self::never())->method('bundleFile');
        $this->emphasizer->expects(self::once())->method('emphasizeFile')->with($file);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::UNIFIED);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelSideBySide(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', static fn() => []);

        $this->commentsModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects(self::never())->method('bundleFile');
        $this->emphasizer->expects(self::once())->method('emphasizeFile')->with($file);
        $this->splitter->expects(self::once())->method('splitFile')->with($file);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::SIDE_BY_SIDE);
        static::assertNotNull($viewModel->leftSideFile);
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

        $this->commentsModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::never())->method('fromDiffFile');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE);
        static::assertNull($viewModel->getHighlightedFile());
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
        $highlightedFile = new HighlightedFile('filepath', static fn() => []);

        $this->commentsModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getReplyCommentViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE);
        static::assertSame($highlightedFile, $viewModel->getHighlightedFile());
    }
}
