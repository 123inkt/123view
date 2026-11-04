<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Entity\User\User;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\FolderCollapseStatusService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\FileTreeViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(FileTreeViewModelProvider::class)]
class FileTreeViewModelProviderTest extends AbstractTestCase
{
    private FileSeenStatusService&MockObject       $fileStatusService;
    private FolderCollapseStatusService&MockObject $folderStatusService;
    private UserEntityProvider&MockObject          $userProvider;
    private CommentVisibility&MockObject           $commentVisibility;
    private FileTreeViewModelProvider              $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileStatusService   = $this->createMock(FileSeenStatusService::class);
        $this->folderStatusService = $this->createMock(FolderCollapseStatusService::class);
        $this->userProvider        = $this->createMock(UserEntityProvider::class);
        $this->commentVisibility   = $this->createMock(CommentVisibility::class);
        $this->provider            = new FileTreeViewModelProvider(
            $this->fileStatusService,
            $this->folderStatusService,
            $this->userProvider,
            $this->commentVisibility,
        );
    }

    public function testGetFileTreeViewModel(): void
    {
        $user           = new User()->setId(10);
        $visibleComment = new Comment();
        $hiddenComment  = new Comment();
        $review         = new CodeReview();
        $review->getComments()->add($hiddenComment);
        $review->getComments()->add($visibleComment);
        /** @var DirectoryTreeNode<DiffFile> $treeNode */
        $treeNode         = new DirectoryTreeNode('foobar');
        $file             = new DiffFile();
        $statusCollection = new FileSeenStatusCollection();
        $folderStatusCollection = new FolderCollapseStatusCollection();

        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->commentVisibility->expects($this->exactly(2))
            ->method('isVisible')
            ->with(...consecutive([$hiddenComment, $user], [$visibleComment, $user]))
            ->willReturn(false, true);
        $this->fileStatusService->expects($this->once())->method('getFileSeenStatus')->with($review)->willReturn($statusCollection);
        $this->folderStatusService->expects($this->once())->method('getFolderCollapseStatus')->with($review)->willReturn($folderStatusCollection);

        $viewModel = $this->provider->getFileTreeViewModel($review, $treeNode, $file);
        static::assertSame($review, $viewModel->review);
        static::assertSame($treeNode, $viewModel->fileTree);
        static::assertSame([$visibleComment], $viewModel->comments->getValues());
    }
}
