<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\FolderCollapseStatusService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FileTreeViewModelProvider::class)]
class FileTreeViewModelProviderTest extends AbstractTestCase
{
    private FileSeenStatusService&MockObject       $fileStatusService;
    private FolderCollapseStatusService&MockObject $folderStatusService;
    private FileTreeViewModelProvider              $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileStatusService   = $this->createMock(FileSeenStatusService::class);
        $this->folderStatusService = $this->createMock(FolderCollapseStatusService::class);
        $this->provider            = new FileTreeViewModelProvider($this->fileStatusService, $this->folderStatusService);
    }

    public function testGetFileTreeViewModel(): void
    {
        $comment = new Comment();
        $review  = new CodeReview();
        $review->getComments()->add($comment);
        /** @var DirectoryTreeNode<DiffFile> $treeNode */
        $treeNode         = new DirectoryTreeNode('foobar');
        $file             = new DiffFile();
        $statusCollection = new FileSeenStatusCollection();
        $folderStatusCollection = new FolderCollapseStatusCollection();

        $this->fileStatusService->expects($this->once())->method('getFileSeenStatus')->with($review)->willReturn($statusCollection);
        $this->folderStatusService->expects($this->once())->method('getFolderCollapseStatus')->with($review)->willReturn($folderStatusCollection);

        $viewModel = $this->provider->getFileTreeViewModel($review, $treeNode, $file);
        static::assertSame($review, $viewModel->review);
        static::assertSame($treeNode, $viewModel->fileTree);
        static::assertSame($review->getComments(), $viewModel->comments);
    }
}
