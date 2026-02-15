<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FileTreeViewModel::class)]
class FileTreeViewModelTest extends AbstractTestCase
{
    private FileSeenStatusCollection&MockObject       $statusCollection;
    private FolderCollapseStatusCollection&MockObject $folderCollection;
    /** @var DirectoryTreeNode<DiffFile>&MockObject */
    private MockObject&DirectoryTreeNode $directoryNode;
    private FileTreeViewModel            $viewModel;
    /** @var ArrayCollection<int, Comment> */
    private ArrayCollection $commentCollection;

    public function setUp(): void
    {
        parent::setUp();
        $this->statusCollection  = $this->createMock(FileSeenStatusCollection::class);
        $this->folderCollection  = $this->createMock(FolderCollapseStatusCollection::class);
        $this->directoryNode     = $this->createMock(DirectoryTreeNode::class);
        $this->commentCollection = new ArrayCollection();
        $this->viewModel         = new FileTreeViewModel(
            new CodeReview(),
            $this->directoryNode,
            $this->commentCollection,
            $this->folderCollection,
            $this->statusCollection,
            new DiffFile()
        );
    }

    public function testGetChangeSummary(): void
    {
        $fileA = $this->createMock(DiffFile::class);
        $fileA->expects($this->once())->method('getNrOfLinesAdded')->willReturn(1);
        $fileA->expects($this->once())->method('getNrOfLinesRemoved')->willReturn(2);
        $fileB = $this->createMock(DiffFile::class);
        $fileB->expects($this->once())->method('getNrOfLinesAdded')->willReturn(3);
        $fileB->expects($this->once())->method('getNrOfLinesRemoved')->willReturn(4);

        $this->directoryNode->expects($this->once())->method('getFileIterator')->willReturn(new ArrayIterator([$fileA, $fileB]));
        $this->statusCollection->expects($this->never())->method('isSeen');
        $this->folderCollection->expects($this->never())->method('isCollapsed');

        static::assertSame(['files' => 2, 'added' => 4, 'removed' => 6], $this->viewModel->getChangeSummary());
    }

    public function testIsFolderCollapsed(): void
    {
        $node = static::createStub(DirectoryTreeNode::class);
        $node->method('getPathname')->willReturn('folder');

        $this->folderCollection->expects($this->once())->method('isCollapsed')->with('folder')->willReturn(true);
        $this->statusCollection->expects($this->never())->method('isSeen');
        $this->directoryNode->expects($this->never())->method('getFileIterator');
        static::assertTrue($this->viewModel->isFolderCollapsed($node));
    }

    #[DataProvider('fileSelectedDataProvider')]
    public function testIsFileSelected(?DiffFile $selectedFile, DiffFile $file, bool $selected): void
    {
        $this->statusCollection->expects($this->never())->method('isSeen');
        $this->folderCollection->expects($this->never())->method('isCollapsed');
        $this->directoryNode->expects($this->never())->method('getFileIterator');
        $viewModel = new FileTreeViewModel(
            new CodeReview(),
            $this->directoryNode,
            $this->commentCollection,
            $this->folderCollection,
            $this->statusCollection,
            $selectedFile
        );
        static::assertSame($selected, $viewModel->isFileSelected($file));
    }

    public static function fileSelectedDataProvider(): Generator
    {
        yield [null, new DiffFile(), false];
        yield [new DiffFile(), new DiffFile(), true];

        $fileA                = new DiffFile();
        $fileA->filePathAfter = 'after1';

        $fileB                = new DiffFile();
        $fileB->filePathAfter = 'after2';
        yield [$fileA, $fileB, false];

        $fileA                = new DiffFile();
        $fileA->filePathAfter = 'after';
        $fileA->hashEnd       = 'end1';

        $fileB                = new DiffFile();
        $fileB->filePathAfter = 'after';
        $fileB->hashEnd       = 'end2';
        yield [$fileA, $fileB, false];
    }

    public function testIsFileSeen(): void
    {
        $this->statusCollection->expects($this->once())->method('isSeen')->with('filepath')->willReturn(true);
        $this->folderCollection->expects($this->never())->method('isCollapsed');
        $this->directoryNode->expects($this->never())->method('getFileIterator');

        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';

        static::assertTrue($this->viewModel->isFileSeen($file));
    }

    public function testGetCommentsForFile(): void
    {
        $this->statusCollection->expects($this->never())->method('isSeen');
        $this->folderCollection->expects($this->never())->method('isCollapsed');
        $this->directoryNode->expects($this->never())->method('getFileIterator');
        $commentA = new Comment();
        $commentB = new Comment();
        $commentC = new Comment();

        $commentA->setFilePath('filepathA');
        $commentB->setFilePath('filepathB');
        $commentC->setFilePath('filepathB');

        $commentA->setState(CommentStateType::OPEN);
        $commentB->setState(CommentStateType::OPEN);
        $commentC->setState(CommentStateType::RESOLVED);

        $this->commentCollection->add($commentA);
        $this->commentCollection->add($commentB);
        $this->commentCollection->add($commentC);

        $file                = new DiffFile();
        $file->filePathAfter = 'filepathB';

        $comments = $this->viewModel->getCommentsForFile($file);
        static::assertSame(['unresolved' => 1, 'total' => 2], $comments);
    }
}
