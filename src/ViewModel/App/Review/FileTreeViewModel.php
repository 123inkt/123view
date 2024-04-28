<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Model\Review\DirectoryTreeNode;

class FileTreeViewModel
{
    /**
     * @param DirectoryTreeNode<DiffFile> $fileTree
     * @param Collection<int, Comment>    $comments
     */
    public function __construct(
        public readonly CodeReview $review,
        public readonly DirectoryTreeNode $fileTree,
        public readonly Collection $comments,
        private readonly FolderCollapseStatusCollection $folderCollapseCollection,
        private readonly FileSeenStatusCollection $fileSeenCollection,
        public readonly ?DiffFile $selectedFile
    ) {
    }

    /**
     * @return array{files: int, added: int, removed: int}
     */
    public function getChangeSummary(): array
    {
        $summary = ['files' => 0, 'added' => 0, 'removed' => 0];
        /** @var DiffFile $file */
        foreach ($this->fileTree->getFileIterator() as $file) {
            ++$summary['files'];
            $summary['added']   += $file->getNrOfLinesAdded();
            $summary['removed'] += $file->getNrOfLinesRemoved();
        }

        return $summary;
    }

    public function isFolderCollapsed(DiffFile $file): bool
    {
        return $this->folderCollapseCollection->isCollapsed($file->filePathBefore);
    }

    public function isFileSeen(DiffFile $file): bool
    {
        return $this->fileSeenCollection->isSeen($file->getFile()?->getPathname() ?? '');
    }

    public function isFileSelected(DiffFile $file): bool
    {
        return
            $this->selectedFile !== null
            && $file->filePathBefore === $this->selectedFile->filePathBefore
            && $file->filePathAfter === $this->selectedFile->filePathAfter
            && $file->hashStart === $this->selectedFile->hashStart
            && $file->hashEnd === $this->selectedFile->hashEnd;
    }

    /**
     * @return array{unresolved: int, total: int}
     */
    public function getCommentsForFile(DiffFile $file): array
    {
        $result = ['unresolved' => 0, 'total' => 0];

        foreach ($this->comments as $comment) {
            if ($comment->getFilePath() !== $file->filePathBefore && $comment->getFilePath() !== $file->filePathAfter) {
                continue;
            }

            if ($comment->getState() !== CommentStateType::RESOLVED) {
                ++$result['unresolved'];
            }
            ++$result['total'];
        }

        return $result;
    }
}
