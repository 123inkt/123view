<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\FileTreeViewModelProvider
 * @covers ::__construct
 */
class FileTreeViewModelProviderTest extends AbstractTestCase
{
    private FileSeenStatusService&MockObject $fileStatusService;
    private FileTreeViewModelProvider        $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileStatusService = $this->createMock(FileSeenStatusService::class);
        $this->provider          = new FileTreeViewModelProvider($this->fileStatusService);
    }

    /**
     * @covers ::getFileTreeViewModel
     */
    public function testGetFileTreeViewModel(): void
    {
        $comment = new Comment();
        $review  = new CodeReview();
        $review->getComments()->add($comment);
        $treeNode         = new DirectoryTreeNode('foobar');
        $file             = new DiffFile();
        $statusCollection = new FileSeenStatusCollection();

        $this->fileStatusService->expects(self::once())->method('getFileSeenStatus')->with($review)->willReturn($statusCollection);

        $viewModel = $this->provider->getFileTreeViewModel($review, $treeNode, $file);
        static::assertSame($review, $viewModel->review);
        static::assertSame($treeNode, $viewModel->fileTree);
        static::assertSame($review->getComments(), $viewModel->comments);
    }
}
