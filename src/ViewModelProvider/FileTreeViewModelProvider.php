<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\FolderCollapseStatusService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\ViewModel\App\Review\FileTreeViewModel;

class FileTreeViewModelProvider
{
    public function __construct(
        private readonly FileSeenStatusService $fileStatusService,
        private readonly FolderCollapseStatusService $folderCollapseService,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentVisibility $commentVisibility,
    ) {
    }

    /**
     * @param DirectoryTreeNode<DiffFile> $treeNode
     */
    public function getFileTreeViewModel(CodeReview $review, DirectoryTreeNode $treeNode, ?DiffFile $selectedFile): FileTreeViewModel
    {
        // hide draft comments from non-authors
        $user     = $this->userProvider->getCurrentUser();
        $comments = $review->getComments()->filter(fn(Comment $comment): bool => $this->commentVisibility->isVisible($comment, $user));

        return new FileTreeViewModel(
            $review,
            $treeNode,
            $comments,
            $this->folderCollapseService->getFolderCollapseStatus($review),
            $this->fileStatusService->getFileSeenStatus($review),
            $selectedFile
        );
    }
}
