<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    public const SIDEBAR_TAB_OVERVIEW  = 'overview';
    public const SIDEBAR_TAB_REVISIONS = 'revisions';

    private string $sidebarTabMode = self::SIDEBAR_TAB_OVERVIEW;

    /**
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(
        private readonly CodeReview $review,
        private readonly FileTreeViewModel $fileTreeModel,
        private readonly FileDiffViewModel $fileDiffViewModel,
        private readonly FormView $addReviewerForm,
        private readonly array $externalLinks
    ) {
    }

    public function setSidebarTabMode(string $sidebarTabMode): void
    {
        $this->sidebarTabMode = $sidebarTabMode;
    }

    public function getSidebarTabMode(): string
    {
        return $this->sidebarTabMode;
    }

    public function getAddReviewerForm(): FormView
    {
        return $this->addReviewerForm;
    }

    public function getFileTreeModel(): FileTreeViewModel
    {
        return $this->fileTreeModel;
    }

    public function getFileDiffViewModel(): FileDiffViewModel
    {
        return $this->fileDiffViewModel;
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
}
