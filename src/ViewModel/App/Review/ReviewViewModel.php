<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\User\User;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    public const SIDEBAR_TAB_OVERVIEW  = 'overview';
    public const SIDEBAR_TAB_REVISIONS = 'revisions';

    private string                   $sidebarTabMode     = self::SIDEBAR_TAB_OVERVIEW;
    private ?FileTreeViewModel       $fileTreeModel      = null;
    private ?ReviewRevisionViewModel $revisionViewModel  = null;
    private ?FormView                $addReviewerForm    = null;
    private bool                     $descriptionVisible = true;
    private ?string                  $highlightedFile    = null;

    public function __construct(public readonly CodeReview $review, public readonly FileDiffViewModel $fileDiffViewModel)
    {
    }

    public function getHighlightedFile(): ?string
    {
        return $this->highlightedFile;
    }

    public function setHighlightedFile(?string $highlightedFile): ReviewViewModel
    {
        $this->highlightedFile = $highlightedFile;

        return $this;
    }

    public function isDescriptionVisible(): bool
    {
        return $this->descriptionVisible;
    }

    public function setDescriptionVisible(bool $descriptionVisible): self
    {
        $this->descriptionVisible = $descriptionVisible;

        return $this;
    }

    public function setSidebarTabMode(string $sidebarTabMode): void
    {
        $this->sidebarTabMode = $sidebarTabMode;
    }

    public function getSidebarTabMode(): string
    {
        return $this->sidebarTabMode;
    }

    public function setAddReviewerForm(?FormView $addReviewerForm): void
    {
        $this->addReviewerForm = $addReviewerForm;
    }

    public function getAddReviewerForm(): ?FormView
    {
        return $this->addReviewerForm;
    }

    public function getRevisionViewModel(): ?ReviewRevisionViewModel
    {
        return $this->revisionViewModel;
    }

    public function setRevisionViewModel(?ReviewRevisionViewModel $revisionViewModel): void
    {
        $this->revisionViewModel = $revisionViewModel;
    }

    public function setFileTreeModel(?FileTreeViewModel $fileTreeModel): void
    {
        $this->fileTreeModel = $fileTreeModel;
    }

    public function getFileTreeModel(): ?FileTreeViewModel
    {
        return $this->fileTreeModel;
    }

    public function getOpenComments(): int
    {
        $count = 0;
        foreach ($this->review->getComments() as $comment) {
            if ($comment->getState() !== CommentStateType::RESOLVED) {
                ++$count;
            }
        }

        return $count;
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

    public function getReviewer(User $user): ?CodeReviewer
    {
        foreach ($this->review->getReviewers() as $reviewer) {
            if ($reviewer->getUser()?->getId() === $user->getId()) {
                return $reviewer;
            }
        }

        return null;
    }
}
