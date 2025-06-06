<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    public const SIDEBAR_TAB_OVERVIEW  = 'overview';
    public const SIDEBAR_TAB_REVISIONS = 'revisions';

    private string                   $sidebarTabMode         = self::SIDEBAR_TAB_OVERVIEW;
    private ?FileTreeViewModel       $fileTreeModel          = null;
    private ?ReviewRevisionViewModel $revisionViewModel      = null;
    private ?ReviewSummaryViewModel  $reviewSummaryViewModel = null;
    private ?FileDiffViewModel       $fileDiffViewModel      = null;
    private ?BranchReviewViewModel   $branchReviewViewModel  = null;
    private ?FormView                $addReviewerForm        = null;
    private bool                     $descriptionVisible     = true;
    private int                      $visibleRevisionCount   = 0;

    /**
     * @param Revision[] $revisions
     */
    public function __construct(public readonly CodeReview $review, public readonly array $revisions)
    {
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

    public function getFileDiffViewModel(): ?FileDiffViewModel
    {
        return $this->fileDiffViewModel;
    }

    public function setFileDiffViewModel(?FileDiffViewModel $fileDiffViewModel): self
    {
        $this->fileDiffViewModel = $fileDiffViewModel;

        return $this;
    }

    public function getReviewSummaryViewModel(): ?ReviewSummaryViewModel
    {
        return $this->reviewSummaryViewModel;
    }

    public function setReviewSummaryViewModel(?ReviewSummaryViewModel $reviewSummaryViewModel): self
    {
        $this->reviewSummaryViewModel = $reviewSummaryViewModel;

        return $this;
    }

    public function getBranchReviewViewModel(): ?BranchReviewViewModel
    {
        return $this->branchReviewViewModel;
    }

    public function setBranchReviewViewModel(?BranchReviewViewModel $branchReviewViewModel): self
    {
        $this->branchReviewViewModel = $branchReviewViewModel;

        return $this;
    }

    public function getVisibleRevisionCount(): int
    {
        return $this->visibleRevisionCount;
    }

    public function setVisibleRevisionCount(int $visibleRevisionCount): self
    {
        $this->visibleRevisionCount = $visibleRevisionCount;

        return $this;
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
        foreach ($this->revisions as $revision) {
            $authors[$revision->getAuthorEmail()] = $revision->getAuthorName();
        }

        return $authors;
    }

    public function getReviewer(User $user): ?CodeReviewer
    {
        foreach ($this->review->getReviewers() as $reviewer) {
            if ($reviewer->getUser()->getId() === $user->getId()) {
                return $reviewer;
            }
        }

        return null;
    }
}
