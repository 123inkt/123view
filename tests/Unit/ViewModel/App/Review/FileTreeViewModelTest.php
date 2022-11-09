<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->statusCollection = $this->createMock(FileSeenStatusCollection::class);
        $this->selectedFile     = new DiffFile();
        $this->viewModel        = new FileTreeViewModel(
            new CodeReview(),
            new DirectoryTreeNode('root'),
            new ArrayCollection(),
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
    }
}
