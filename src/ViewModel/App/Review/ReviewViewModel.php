<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\ViewModel\App\Review\Timeline\TimelineViewModel;
use DR\GitCommitNotification\ViewModel\App\Revision\ReviewRevisionViewModel;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    public const SIDEBAR_TAB_OVERVIEW  = 'overview';
    public const SIDEBAR_TAB_REVISIONS = 'revisions';

    private string                   $sidebarTabMode     = self::SIDEBAR_TAB_OVERVIEW;
    private ?FileTreeViewModel       $fileTreeModel      = null;
    private ?ReviewRevisionViewModel $revisionViewModel  = null;
    private ?TimelineViewModel       $timelineViewModel  = null;
    private ?FileDiffViewModel       $fileDiffViewModel  = null;
    private ?FormView                $addReviewerForm    = null;
    private bool                     $descriptionVisible = true;

    public function __construct(public readonly CodeReview $review)
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

    public function getTimelineViewModel(): ?TimelineViewModel
    {
        return $this->timelineViewModel;
    }

    public function setTimelineViewModel(?TimelineViewModel $timelineViewModel): self
    {
        $this->timelineViewModel = $timelineViewModel;

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
