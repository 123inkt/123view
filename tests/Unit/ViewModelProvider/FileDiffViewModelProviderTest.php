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
use DR\Review\ViewModel\App\Review\CodeQualityViewModel;
use DR\Review\ViewModel\App\Review\HighlightFileViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModelProvider\CodeQualityViewModelProvider;
use DR\Review\ViewModelProvider\CommentsViewModelProvider;
use DR\Review\ViewModelProvider\CommentViewModelProvider;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(FileDiffViewModelProvider::class)]
class FileDiffViewModelProviderTest extends AbstractTestCase
{
    private CommentViewModelProvider&MockObject        $commentModelProvider;
    private CommentsViewModelProvider&MockObject       $commentsModelProvider;
    private CacheableHighlightedFileService&MockObject $highlightedFileService;
    private UnifiedDiffBundler&MockObject              $bundler;
    private UnifiedDiffEmphasizer&MockObject           $emphasizer;
    private UnifiedDiffSplitter&MockObject             $splitter;
    private CodeQualityViewModelProvider&MockObject    $inspectionModelProvider;
    private FileDiffViewModelProvider                  $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentModelProvider    = $this->createMock(CommentViewModelProvider::class);
        $this->commentsModelProvider   = $this->createMock(CommentsViewModelProvider::class);
        $this->highlightedFileService  = $this->createMock(CacheableHighlightedFileService::class);
        $this->bundler                 = $this->createMock(UnifiedDiffBundler::class);
        $this->emphasizer              = $this->createMock(UnifiedDiffEmphasizer::class);
        $this->splitter                = $this->createMock(UnifiedDiffSplitter::class);
        $this->inspectionModelProvider = $this->createMock(CodeQualityViewModelProvider::class);
        $this->provider                = new FileDiffViewModelProvider(
            $this->commentModelProvider,
            $this->commentsModelProvider,
            $this->highlightedFileService,
            $this->bundler,
            $this->emphasizer,
            $this->splitter,
            $this->inspectionModelProvider
        );
    }

    /**
     * @throws Throwable
     */
    public function testGetFileDiffViewModelInline(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile     = new HighlightedFile('filepath', static fn() => []);
        $inspectionViewModel = new CodeQualityViewModel([], null);

        $this->commentsModelProvider->expects($this->once())->method('getCommentsViewModel')->with($review, null, $file);
        $this->highlightedFileService->expects($this->once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects($this->once())->method('bundleFile')->with($file);
        $this->emphasizer->expects($this->never())->method('emphasizeFile');
        $this->inspectionModelProvider->expects($this->once())->method('getCodeQualityViewModel')->with($review)->willReturn($inspectionViewModel);
        $this->commentModelProvider->expects($this->never())->method('getReplyCommentViewModel');
        $this->splitter->expects($this->never())->method('splitFile');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE, 6);
        static::assertEquals(new HighlightFileViewModel($highlightedFile), $viewModel->getHighlightedFileViewModel());
        static::assertSame($inspectionViewModel, $viewModel->getCodeQualityViewModel());
    }

    /**
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

        $this->commentsModelProvider->expects($this->once())->method('getCommentsViewModel')->with($review, null, $file);
        $this->highlightedFileService->expects($this->once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects($this->never())->method('bundleFile');
        $this->emphasizer->expects($this->once())->method('emphasizeFile')->with($file);
        $this->commentModelProvider->expects($this->never())->method('getReplyCommentViewModel');
        $this->splitter->expects($this->never())->method('splitFile');
        $this->inspectionModelProvider->expects($this->never())->method('getCodeQualityViewModel');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::UNIFIED, 6);
        static::assertEquals(new HighlightFileViewModel($highlightedFile), $viewModel->getHighlightedFileViewModel());
    }

    /**
     * @throws Throwable
     */
    public function testGetFileDiffViewModelSideBySide(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';
        $leftSideFile        = new DiffFile();
        $repository          = new Repository();
        $review              = new CodeReview();
        $review->setRepository($repository);
        $highlightedFile = new HighlightedFile('filepath', static fn() => []);

        $this->commentsModelProvider->expects($this->once())->method('getCommentsViewModel')->with($review, $leftSideFile, $file);
        $this->highlightedFileService->expects($this->once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->bundler->expects($this->never())->method('bundleFile');
        $this->emphasizer->expects($this->once())->method('emphasizeFile')->with($file);
        $this->splitter->expects($this->once())->method('splitFile')->with($file)->willReturn($leftSideFile);
        $this->commentModelProvider->expects($this->never())->method('getReplyCommentViewModel');
        $this->inspectionModelProvider->expects($this->never())->method('getCodeQualityViewModel');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::SIDE_BY_SIDE, 6);
        static::assertNotNull($viewModel->leftSideFile);
        static::assertEquals(new HighlightFileViewModel($highlightedFile), $viewModel->getHighlightedFileViewModel());
    }

    /**
     * @throws Throwable
     */
    public function testGetFileDiffViewModelNoHighlightIfFileIsDeleted(): void
    {
        $file                 = new DiffFile();
        $file->filePathBefore = 'filepath';
        $review               = new CodeReview();

        $this->commentsModelProvider->expects($this->once())->method('getCommentsViewModel')->with($review, null, $file);
        $this->highlightedFileService->expects($this->never())->method('fromDiffFile');
        $this->commentModelProvider->expects($this->never())->method('getReplyCommentViewModel');
        $this->bundler->expects($this->never())->method('bundleFile');
        $this->emphasizer->expects($this->never())->method('emphasizeFile');
        $this->splitter->expects($this->never())->method('splitFile');
        $this->inspectionModelProvider->expects($this->never())->method('getCodeQualityViewModel');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE, 6);
        static::assertNull($viewModel->getHighlightedFileViewModel());
    }

    /**
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

        $this->commentsModelProvider->expects($this->once())->method('getCommentsViewModel')->with($review, null, $file);
        $this->highlightedFileService->expects($this->once())->method('fromDiffFile')->with($repository, $file)->willReturn($highlightedFile);
        $this->commentModelProvider->expects($this->once())->method('getReplyCommentViewModel')->with($action);
        $this->bundler->expects($this->never())->method('bundleFile');
        $this->emphasizer->expects($this->never())->method('emphasizeFile');
        $this->splitter->expects($this->never())->method('splitFile');
        $this->inspectionModelProvider->expects($this->never())->method('getCodeQualityViewModel');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE, 6);
        static::assertEquals(new HighlightFileViewModel($highlightedFile), $viewModel->getHighlightedFileViewModel());
    }
}
