<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    /**
     * @param DiffFile[] $files
     */
    public function __construct(
        private readonly CodeReview $review,
        private readonly array $files,
        private readonly DiffFile $selectedFile,
        private readonly FormView $addReviewerForm
    ) {
    }

    public function getAddReviewerForm(): FormView
    {
        return $this->addReviewerForm;
    }

    /**
     * @return DiffFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
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
            $authors[$revision->getAuthorEmail()] = $revision->getAuthorName();
        }

        return $authors;
    }

    public function getReview(): CodeReview
    {
        return $this->review;
    }
}
