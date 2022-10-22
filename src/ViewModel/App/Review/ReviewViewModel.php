<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use Symfony\Component\Form\FormView;

class ReviewViewModel
{
    private ?CommentsViewModel         $commentsViewModel    = null;
    private ?AddCommentViewModel       $addCommentForm       = null;
    private ?EditCommentViewModel      $editCommentForm      = null;
    private ?ReplyCommentViewModel     $replyCommentForm     = null;
    private ?EditCommentReplyViewModel $editReplyCommentForm = null;

    /**
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(
        private readonly CodeReview $review,
        private readonly FileTreeViewModel $fileTreeModel,
        private readonly ?DiffFile $selectedFile,
        private readonly FormView $addReviewerForm,
        private readonly array $externalLinks
    ) {
    }

    public function getAddReviewerForm(): FormView
    {
        return $this->addReviewerForm;
    }

    public function setAddCommentForm(AddCommentViewModel $addCommentForm): self
    {
        $this->addCommentForm = $addCommentForm;

        return $this;
    }

    public function getAddCommentForm(): ?AddCommentViewModel
    {
        return $this->addCommentForm;
    }

    public function getCommentsViewModel(): ?CommentsViewModel
    {
        return $this->commentsViewModel;
    }

    public function getEditCommentForm(): ?EditCommentViewModel
    {
        return $this->editCommentForm;
    }

    public function setEditCommentForm(?EditCommentViewModel $editCommentForm): void
    {
        $this->editCommentForm = $editCommentForm;
    }

    public function setCommentsViewModel(?CommentsViewModel $commentsViewModel): void
    {
        $this->commentsViewModel = $commentsViewModel;
    }

    public function getReplyCommentForm(): ?ReplyCommentViewModel
    {
        return $this->replyCommentForm;
    }

    public function setReplyCommentForm(?ReplyCommentViewModel $replyCommentForm): void
    {
        $this->replyCommentForm = $replyCommentForm;
    }

    public function getEditReplyCommentForm(): ?EditCommentReplyViewModel
    {
        return $this->editReplyCommentForm;
    }

    public function setEditReplyCommentForm(?EditCommentReplyViewModel $editReplyCommentForm): void
    {
        $this->editReplyCommentForm = $editReplyCommentForm;
    }

    public function getFileTreeModel(): FileTreeViewModel
    {
        return $this->fileTreeModel;
    }

    public function getSelectedFile(): ?DiffFile
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

        foreach ($file->getBlocks() as $block) {
            foreach ($block->lines as $line) {
                $lineNumber = (string)($before ? $line->lineNumberBefore : $line->lineNumberAfter);
                $length     = max($length, strlen($lineNumber));
            }
        }

        return $length;
    }
}
