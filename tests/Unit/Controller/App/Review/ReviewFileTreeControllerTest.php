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
use DR\Review\Service\Git\Review\ReviewSessionService;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\ReviewFileTreeController
 * @covers ::__construct
 */
class ReviewFileTreeControllerTest extends AbstractControllerTestCase
{
    private FileTreeViewModelProvider&MockObject $viewModelProvider;
    private CodeReviewFileService&MockObject     $fileService;
    private ReviewSessionService&MockObject      $sessionService;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(FileTreeViewModelProvider::class);
        $this->fileService       = $this->createMock(CodeReviewFileService::class);
        $this->sessionService    = $this->createMock(ReviewSessionService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request  = new Request(['filePath' => 'filePath']);
        $revision = new Revision();
        $review   = new CodeReview();
        $review->getRevisions()->add($revision);

        /** @var DirectoryTreeNode<DiffFile> $treeNode */
        $treeNode     = new DirectoryTreeNode('node');
        $selectedFile = new DiffFile();
        $viewModel    = $this->createMock(FileTreeViewModel::class);

        $this->fileService->expects(self::once())
            ->method('getFiles')
            ->with($review, [$revision], 'filePath', DiffComparePolicy::ALL)
            ->willReturn([$treeNode, $selectedFile]);
        $this->sessionService->expects(self::once())->method('getDiffComparePolicyForUser')->willReturn(DiffComparePolicy::ALL);
        $this->viewModelProvider->expects(self::once())
            ->method('getFileTreeViewModel')
            ->with($review, $treeNode, $selectedFile)
            ->willReturn($viewModel);

        $result = ($this->controller)($request, $review);
        static::assertSame(['fileTreeModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new ReviewFileTreeController($this->viewModelProvider, $this->fileService, $this->sessionService);
    }
}
