<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use DR\Review\ViewModelProvider\ReviewSummaryViewModelProvider;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

#[CoversClass(ReviewViewModelProvider::class)]
class ReviewViewModelProviderTest extends AbstractTestCase
{
    private FileDiffViewModelProvider&MockObject      $fileDiffProvider;
    private CodeReviewFileService&MockObject          $fileService;
    private FormFactoryInterface&MockObject           $formFactory;
    private FileTreeViewModelProvider&MockObject      $fileTreeModelProvider;
    private RevisionViewModelProvider&MockObject      $revisionModelProvider;
    private CodeReviewTypeDecider&MockObject          $reviewTypeDecider;
    private ReviewSummaryViewModelProvider&MockObject $summaryViewModelProvider;
    private ReviewViewModelProvider&MockObject      $revisionService;
    private RevisionVisibilityService&MockObject $visibilityService;
    private ReviewViewModelProvider              $modelProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileDiffProvider         = $this->createMock(FileDiffViewModelProvider::class);
        $this->fileService              = $this->createMock(CodeReviewFileService::class);
        $this->formFactory              = $this->createMock(FormFactoryInterface::class);
        $this->fileTreeModelProvider    = $this->createMock(FileTreeViewModelProvider::class);
        $this->revisionModelProvider    = $this->createMock(RevisionViewModelProvider::class);
        $this->reviewTypeDecider        = $this->createMock(CodeReviewTypeDecider::class);
        $this->summaryViewModelProvider = $this->createMock(ReviewSummaryViewModelProvider::class);
        $this->revisionService          = $this->createMock(ReviewViewModelProvider::class);
        $this->visibilityService        = $this->createMock(RevisionVisibilityService::class);
        $this->modelProvider            = new ReviewViewModelProvider(
            $this->fileDiffProvider,
            $this->formFactory,
            $this->fileService,
            $this->reviewTypeDecider,
            $this->fileTreeModelProvider,
            $this->revisionModelProvider,
            $this->summaryViewModelProvider,
            $this->revisionService,
            $this->visibilityService
        );
    }

    /**
     * @throws Throwable
     */
    public function testGetViewModelSidebarOverview(): void
    {
        $action     = new EditCommentAction(new Comment());
        $filePath   = '/path/to/file';
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $file                 = new DiffFile();
        $file->filePathBefore = 'before';
        $file->filePathAfter  = 'after';
        $tree                 = new DirectoryTreeNode('foobar');
        $tree->addNode(['path', 'to', 'file.txt'], $file);

        $request = $this->createMock(ReviewRequest::class);
        $this->reviewTypeDecider->expects($this->once())->method('decide')
            ->with($review, [$revision], [$revision])
            ->willReturn(CodeReviewType::BRANCH);
        $this->revisionService->expects($this->once())->method('getViewModel')->with($review)->willReturn(new ReviewViewModel($review, [$revision]));
        $this->visibilityService->expects($this->once())
            ->method('getVisibleRevisions')
            ->with($review, [$revision])
            ->willReturnArgument(1);
        $request->expects($this->once())->method('getFilePath')->willReturn($filePath);
        $request->expects($this->exactly(3))->method('getTab')->willReturn(ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
        $request->expects($this->once())->method('getAction')->willReturn($action);
        $request->expects($this->exactly(2))->method('getComparisonPolicy')->willReturn(DiffComparePolicy::IGNORE);
        $request->expects($this->once())->method('getDiffMode')->willReturn(ReviewDiffModeEnum::INLINE);

        $this->fileService->expects($this->once())->method('getFiles')->with($review, [$revision], $filePath)->willReturn([$tree, $file]);
        $this->fileDiffProvider
            ->expects($this->once())
            ->method('getFileDiffViewModel')
            ->with($review, $file, $action, DiffComparePolicy::IGNORE, ReviewDiffModeEnum::INLINE);
        $this->formFactory->expects($this->once())->method('create')->with(AddReviewerFormType::class, null, ['review' => $review]);
        $this->fileTreeModelProvider->expects($this->once())->method('getFileTreeViewModel')->with($review, $tree, $file);

        $viewModel = $this->modelProvider->getViewModel($review, $request);
        static::assertFalse($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getAddReviewerForm());
        static::assertNotNull($viewModel->getFileTreeModel());
    }

    /**
     * @throws Throwable
     */
    public function testGetViewModelWithoutSelectedFile(): void
    {
        $filePath   = '/path/to/file';
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $file = new DiffFile();
        $tree = new DirectoryTreeNode('foobar');
        $tree->addNode(['path', 'to', 'file.txt'], $file);

        $request = $this->createMock(ReviewRequest::class);
        $request->expects($this->once())->method('getFilePath')->willReturn($filePath);
        $request->expects($this->exactly(3))->method('getTab')->willReturn(ReviewViewModel::SIDEBAR_TAB_REVISIONS);

        $this->reviewTypeDecider->expects($this->once())->method('decide')
            ->with($review, [$revision], [$revision])
            ->willReturn(CodeReviewType::BRANCH);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->visibilityService->expects($this->once())
            ->method('getVisibleRevisions')
            ->with($review, [$revision])
            ->willReturnArgument(1);
        $this->fileService->expects($this->once())
            ->method('getFiles')
            ->with($review, [$revision], $filePath, new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, DiffComparePolicy::IGNORE, 'branch'))
            ->willReturn([$tree, null]);
        $request->expects($this->once())->method('getComparisonPolicy')->willReturn(DiffComparePolicy::IGNORE);
        $this->summaryViewModelProvider->expects($this->once())->method('getSummaryViewModel')->with($review, [$revision], $tree);
        $this->fileDiffProvider->expects(self::never())->method('getFileDiffViewModel');
        $this->revisionModelProvider->expects($this->once())->method('getRevisionViewModel')->with($review, [$revision]);

        $viewModel = $this->modelProvider->getViewModel($review, $request);
        static::assertTrue($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getReviewSummaryViewModel());
        static::assertNull($viewModel->getFileDiffViewModel());
        static::assertNotNull($viewModel->getRevisionViewModel());
    }
}
