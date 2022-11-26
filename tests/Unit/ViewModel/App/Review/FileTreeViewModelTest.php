<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\FileSeenStatusCollection;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel
 * @covers ::__construct
 */
class FileTreeViewModelTest extends AbstractTestCase
{
    private FileSeenStatusCollection&MockObject $statusCollection;
    private DiffFile                            $selectedFile;
    private FileTreeViewModel                   $viewModel;
    /** @var ArrayCollection<int, Comment> */
    private ArrayCollection $commentCollection;

    public function setUp(): void
    {
        parent::setUp();
        $this->statusCollection  = $this->createMock(FileSeenStatusCollection::class);
        $this->selectedFile      = new DiffFile();
        $this->commentCollection = new ArrayCollection();
        $this->viewModel         = new FileTreeViewModel(
            new CodeReview(),
            new DirectoryTreeNode('root'),
            $this->commentCollection,
            $this->statusCollection,
            $this->selectedFile
        );
    }

    /**
     * @covers ::isFileSelected
     */
    public function testIsFileSelected(): void
    {
        static::assertFalse($this->viewModel->isFileSelected(new DiffFile()));
        static::assertTrue($this->viewModel->isFileSelected($this->selectedFile));
    }

    /**
     * @covers ::isFileSeen
     */
    public function testIsFileSeen(): void
    {
        $this->statusCollection->expects(self::once())->method('isSeen')->with('filepath')->willReturn(true);

        $file                = new DiffFile();
        $file->filePathAfter = 'filepath';

        static::assertTrue($this->viewModel->isFileSeen($file));
    }

    /**
     * @covers ::getCommentsForFile
     */
    public function testGetCommentsForFile(): void
    {
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
