<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ReviewFileTreeController;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewSessionService;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<ReviewFileTreeController>
 */
#[CoversClass(ReviewFileTreeController::class)]
class ReviewFileTreeControllerTest extends AbstractControllerTestCase
{
    private FileTreeViewModelProvider&MockObject $viewModelProvider;
    private CodeReviewFileService&MockObject     $fileService;
    private ReviewSessionService&MockObject      $sessionService;
    private CodeReviewRevisionService&MockObject $revisionService;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(FileTreeViewModelProvider::class);
        $this->fileService       = $this->createMock(CodeReviewFileService::class);
        $this->sessionService    = $this->createMock(ReviewSessionService::class);
        $this->revisionService   = $this->createMock(CodeReviewRevisionService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request  = new Request(['filePath' => 'filePath']);
        $revision = new Revision();
        $review   = new CodeReview();

        /** @var DirectoryTreeNode<DiffFile> $treeNode */
        $treeNode     = new DirectoryTreeNode('node');
        $selectedFile = new DiffFile();
        $viewModel    = $this->createMock(FileTreeViewModel::class);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->fileService->expects($this->once())
            ->method('getFiles')
            ->with($review, [$revision], 'filePath', new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, DiffComparePolicy::ALL))
            ->willReturn([$treeNode, $selectedFile]);
        $this->sessionService->expects($this->once())->method('getDiffComparePolicyForUser')->willReturn(DiffComparePolicy::ALL);
        $this->viewModelProvider->expects($this->once())
            ->method('getFileTreeViewModel')
            ->with($review, $treeNode, $selectedFile)
            ->willReturn($viewModel);

        $result = ($this->controller)($request, $review);
        static::assertSame(['fileTreeModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new ReviewFileTreeController($this->viewModelProvider, $this->fileService, $this->sessionService, $this->revisionService);
    }
}
