<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\FolderCollapseService;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;

class FileTreeViewModelProvider
{
    public function __construct(
        private readonly FileSeenStatusService $fileStatusService,
        private readonly FolderCollapseService $folderCollapseService
    )
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
            $this->folderCollapseService->getFolderCollapseStatus($review),
            $this->fileStatusService->getFileSeenStatus($review),
            $selectedFile
        );
    }
}
