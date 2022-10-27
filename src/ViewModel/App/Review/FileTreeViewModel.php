<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use Doctrine\Common\Collections\Collection;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;

class FileTreeViewModel
{
    /**
     * @param DirectoryTreeNode<DiffFile> $fileTree
     * @param Collection<int, Comment>    $comments
     */
    public function __construct(public readonly DirectoryTreeNode $fileTree, public readonly Collection $comments, private ?DiffFile $selectedFile)
    {
    }

    public function isFileSelected(DiffFile $file): bool
    {
        return $file === $this->selectedFile;
    }

    /**
     * @return array{unresolved: int, total: int}
     */
    public function getCommentsForFile(DiffFile $file): array
    {
        $result = ['unresolved' => 0, 'total' => 0];

        foreach ($this->comments as $comment) {
            if ($comment->getFilePath() !== ($file->filePathBefore ?? $file->filePathAfter)) {
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
