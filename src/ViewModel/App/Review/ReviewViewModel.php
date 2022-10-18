<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\DirectoryTreeNode;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    /**
     * @param DirectoryTreeNode<DiffFile> $fileTree
     * @param ExternalLink[]              $externalLinks
     */
    public function __construct(
        private readonly CodeReview $review,
        private readonly DirectoryTreeNode $fileTree,
        private readonly DiffFile $selectedFile,
        private readonly FormView $addReviewerForm,
        private readonly array $externalLinks
    ) {
    }

    public function getAddReviewerForm(): FormView
    {
        return $this->addReviewerForm;
    }

    /**
     * @return DirectoryTreeNode<DiffFile>
     */
    public function getFileTree(): DirectoryTreeNode
    {
        return $this->fileTree;
    }

    public function getSelectedFile(): DiffFile
    {
        return $this->selectedFile;
    }

    /**
     * @return array<string, string>
     */
    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->review->getRevisions() as $revision) {
            $authors[(string)$revision->getAuthorEmail()] = (string)$revision->getAuthorName();
        }

        return $authors;
    }

    public function isReviewer(User $user): bool
    {
        foreach ($this->review->getReviewers() as $reviewer) {
            if ($reviewer->getUser()?->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getReview(): CodeReview
    {
        return $this->review;
    }

    /**
     * @return ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
    }

    /**
     * For the given block of changes, determine the maximum string length of the line numbers.
     *
     * @param bool $before if true, take the `before` line numbers, `after` otherwise.
     */
    public function getMaxLineNumberLength(?DiffFile $file, bool $before): int
    {
        if ($file === null) {
            return 0;
        }

        $length = 0;

        foreach ($file->blocks as $block) {
            foreach ($block->lines as $line) {
                $lineNumber = (string)($before ? $line->lineNumberBefore : $line->lineNumberAfter);
                $length     = max($length, strlen($lineNumber));
            }
        }

        return $length;
    }
}
