<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\ViewModel\App\Review\FileTreeViewModel;

class FileTreeViewModelProvider
{
    public function __construct(private readonly FileSeenStatusService $fileStatusService)
    {
    }

    /**
     * @param DirectoryTreeNode<DiffFile> $treeNode
     */
    public function getFileTreeViewModel(CodeReview $review, DirectoryTreeNode $treeNode, ?DiffFile $selectedFile): FileTreeViewModel
    {
        return new FileTreeViewModel(
            $review,
            $treeNode,
            $review->getComments(),
            $this->fileStatusService->getFileSeenStatus($review),
            $selectedFile
        );
    }
}
