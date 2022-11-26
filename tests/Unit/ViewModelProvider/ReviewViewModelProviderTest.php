<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use DR\GitCommitNotification\ViewModelProvider\FileDiffViewModelProvider;
use DR\GitCommitNotification\ViewModelProvider\FileTreeViewModelProvider;
use DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider;
use DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\ReviewViewModelProvider
 * @covers ::__construct
 */
class ReviewViewModelProviderTest extends AbstractTestCase
{
    private FileDiffViewModelProvider&MockObject  $fileDiffProvider;
    private ReviewDiffServiceInterface&MockObject $reviewDiffService;
    private FormFactoryInterface&MockObject       $formFactory;
    private FileTreeGenerator&MockObject          $treeGenerator;
    private FileTreeViewModelProvider&MockObject  $fileTreeModelProvider;
    private RevisionViewModelProvider&MockObject  $revisionModelProvider;
    private DiffFinder&MockObject                 $diffFinder;
    private ReviewViewModelProvider               $modelProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileDiffProvider      = $this->createMock(FileDiffViewModelProvider::class);
        $this->reviewDiffService     = $this->createMock(ReviewDiffServiceInterface::class);
        $this->formFactory           = $this->createMock(FormFactoryInterface::class);
        $this->treeGenerator         = $this->createMock(FileTreeGenerator::class);
        $this->fileTreeModelProvider = $this->createMock(FileTreeViewModelProvider::class);
        $this->revisionModelProvider = $this->createMock(RevisionViewModelProvider::class);
        $this->diffFinder            = $this->createMock(DiffFinder::class);
        $this->modelProvider         = new ReviewViewModelProvider(
            $this->fileDiffProvider,
            $this->reviewDiffService,
            $this->formFactory,
            $this->treeGenerator,
            $this->fileTreeModelProvider,
            $this->revisionModelProvider,
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

        $this->reviewDiffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->treeGenerator->expects(self::once())->method('generate')->with([$file])->willReturn($tree);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], $filePath)->willReturn($file);
        $this->fileDiffProvider->expects(self::once())->method('getFileDiffViewModel')->with($review, $file, $action);
        $this->formFactory->expects(self::once())->method('create')->with(AddReviewerFormType::class, null, ['review' => $review]);
        $this->fileTreeModelProvider->expects(self::once())->method('getFileTreeViewModel')->with($review, $tree, $file);

        $viewModel = $this->modelProvider->getViewModel($review, $filePath, ReviewViewModel::SIDEBAR_TAB_OVERVIEW, $action);
        static::assertFalse($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getAddReviewerForm());
        static::assertNotNull($viewModel->getFileTreeModel());
    }

    /**
     * @covers ::getViewModel
     * @throws Throwable
     */
    public function testGetViewModelRevisionOverview(): void
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

        $this->reviewDiffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->treeGenerator->expects(self::once())->method('generate')->with([$file])->willReturn($tree);
        $this->diffFinder->expects(self::once())->method('findFileByPath')->with([$file], $filePath)->willReturn(null);
        $this->fileDiffProvider->expects(self::once())->method('getFileDiffViewModel')->with($review, $file, $action);
        $this->revisionModelProvider->expects(self::once())->method('getRevisionViewModel')->with($review, [$revision]);

        $viewModel = $this->modelProvider->getViewModel($review, $filePath, ReviewViewModel::SIDEBAR_TAB_REVISIONS, $action);
        static::assertFalse($viewModel->isDescriptionVisible());
        static::assertNotNull($viewModel->getRevisionViewModel());
    }
}
