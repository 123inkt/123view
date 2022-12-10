<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\Revision;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\DiffFinder;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use DR\Review\ViewModelProvider\ReviewTimelineViewModelProvider;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\ViewModelProvider\ReviewViewModelProvider
 * @covers ::__construct
 */
class ReviewViewModelProviderTest extends AbstractTestCase
{
    private FileDiffViewModelProvider&MockObject       $fileDiffProvider;
    private ReviewDiffServiceInterface&MockObject      $reviewDiffService;
    private FormFactoryInterface&MockObject            $formFactory;
    private FileTreeGenerator&MockObject               $treeGenerator;
    private FileTreeViewModelProvider&MockObject       $fileTreeModelProvider;
    private RevisionViewModelProvider&MockObject       $revisionModelProvider;
    private DiffFinder&MockObject                      $diffFinder;
    private ReviewTimelineViewModelProvider&MockObject $timelineViewModelProvider;
    private ReviewViewModelProvider                    $modelProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileDiffProvider          = $this->createMock(FileDiffViewModelProvider::class);
        $this->reviewDiffService         = $this->createMock(ReviewDiffServiceInterface::class);
        $this->formFactory               = $this->createMock(FormFactoryInterface::class);
        $this->treeGenerator             = $this->createMock(FileTreeGenerator::class);
        $this->fileTreeModelProvider     = $this->createMock(FileTreeViewModelProvider::class);
        $this->revisionModelProvider     = $this->createMock(RevisionViewModelProvider::class);
        $this->timelineViewModelProvider = $this->createMock(ReviewTimelineViewModelProvider::class);
        $this->diffFinder                = $this->createMock(DiffFinder::class);
        $this->modelProvider             = new ReviewViewModelProvider(
            $this->fileDiffProvider,
            $this->reviewDiffService,
            $this->formFactory,
            $this->treeGenerator,
            $this->fileTreeModelProvider,
            $this->revisionModelProvider,
            $this->timelineViewModelProvider,
            $this->diffFinder
        );
    }

    /**
     * @covers ::getViewModel
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
        $review->getRevisions()->add($revision);
        $file = new DiffFile();
        $tree = new DirectoryTreeNode('foobar');
        $tree->addNode(['path', 'to', 'file.txt'], $file);

        $request = $this->createMock(ReviewRequest::class);
        $request->expects(self::once())->method('getFilePath')->willReturn($filePath);
        $request->expects(self::exactly(3))->method('getTab')->willReturn(ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
        $request->expects(self::once())->method('getAction')->willReturn($action);
        $request->expects(self::once())->method('getDiffMode')->willReturn(ReviewDiffModeEnum::INLINE);

        $this->reviewDiffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->treeGenerator->expects(self::once())->method('generate')->with([$file])->willReturn($tree);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], $filePath)->willReturn($file);
        $this->fileDiffProvider->expects(self::once())->method('getFileDiffViewModel')->with($review, $file, $action, ReviewDiffModeEnum::INLINE);
        $this->formFactory->expects(self::once())->method('create')->with(AddReviewerFormType::class, null, ['review' => $review]);
        $this->fileTreeModelProvider->expects(self::once())->method('getFileTreeViewModel')->with($review, $tree, $file);

        $viewModel = $this->modelProvider->getViewModel($review, $request);
        static::assertFalse($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getAddReviewerForm());
        static::assertNotNull($viewModel->getFileTreeModel());
    }

    /**
     * @covers ::getViewModel
     * @throws Throwable
     */
    public function testGetViewModelWithoutSelectedFile(): void
    {
        $filePath   = '/path/to/file';
        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);
        $file = new DiffFile();
        $tree = new DirectoryTreeNode('foobar');
        $tree->addNode(['path', 'to', 'file.txt'], $file);

        $request = $this->createMock(ReviewRequest::class);
        $request->expects(self::once())->method('getFilePath')->willReturn($filePath);
        $request->expects(self::exactly(3))->method('getTab')->willReturn(ReviewViewModel::SIDEBAR_TAB_REVISIONS);

        $this->reviewDiffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->treeGenerator->expects(self::once())->method('generate')->with([$file])->willReturn($tree);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], $filePath)->willReturn(null);
        $this->timelineViewModelProvider->expects(self::once())->method('getTimelineViewModel')->with($review);
        $this->fileDiffProvider->expects(self::never())->method('getFileDiffViewModel');
        $this->revisionModelProvider->expects(self::once())->method('getRevisionViewModel')->with($review, [$revision]);

        $viewModel = $this->modelProvider->getViewModel($review, $request);
        static::assertTrue($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getTimelineViewModel());
        static::assertNull($viewModel->getFileDiffViewModel());
        static::assertNotNull($viewModel->getRevisionViewModel());
    }
}
