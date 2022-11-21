<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\CommentViewModelProvider;
use DR\GitCommitNotification\ViewModelProvider\FileDiffViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\FileDiffViewModelProvider
 * @covers ::__construct
 */
class FileDiffViewModelProviderTest extends AbstractTestCase
{
    private CommentViewModelProvider&MockObject        $commentModelProvider;
    private CacheableHighlightedFileService&MockObject $highlightedFileService;
    private FileDiffViewModelProvider                  $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentModelProvider   = $this->createMock(CommentViewModelProvider::class);
        $this->highlightedFileService = $this->createMock(CacheableHighlightedFileService::class);
        $this->provider               = new FileDiffViewModelProvider($this->commentModelProvider, $this->highlightedFileService);
    }

    /**
     * @covers ::getFileDiffViewModel
     * @throws Throwable
     */
    public function testGetFileDiffViewModelWithoutSelectedFile(): void
    {
        $action = new AddCommentAction(new LineReference('reference', 1, 2, 3));
        $review = new CodeReview();

        $this->commentModelProvider->expects(self::never())->method('getCommentsViewModel');

        $viewModel = $this->provider->getFileDiffViewModel($review, null, $action);
        static::assertNull($viewModel->getHighlightedFile());
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
        $revision            = new Revision();
        $review              = new CodeReview();
        $review->getRevisions()->add($revision);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromRevision')->with($revision, 'filepath')->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getAddCommentViewModel')->with($review, $file, $action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action);
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
        $revision            = new Revision();
        $review              = new CodeReview();
        $review->getRevisions()->add($revision);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromRevision')->with($revision, 'filepath')->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getEditCommentViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action);
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
        $revision            = new Revision();
        $review              = new CodeReview();
        $review->getRevisions()->add($revision);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromRevision')->with($revision, 'filepath')->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getReplyCommentViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action);
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
        $revision            = new Revision();
        $review              = new CodeReview();
        $review->getRevisions()->add($revision);
        $highlightedFile = new HighlightedFile('filepath', []);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::once())->method('fromRevision')->with($revision, 'filepath')->willReturn($highlightedFile);
        $this->commentModelProvider->expects(self::once())->method('getEditCommentReplyViewModel')->with($action);

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, $action);
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
        $revision             = new Revision();
        $review               = new CodeReview();
        $review->getRevisions()->add($revision);

        $this->commentModelProvider->expects(self::once())->method('getCommentsViewModel')->with($review, $file);
        $this->highlightedFileService->expects(self::never())->method('fromRevision');

        $viewModel = $this->provider->getFileDiffViewModel($review, $file, null);
        static::assertNull($viewModel->getHighlightedFile());
    }
}
