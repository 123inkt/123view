<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;

class FileDiffViewModel
{
    private ?CommentsViewModel         $commentsViewModel    = null;
    private ?AddCommentViewModel       $addCommentForm       = null;
    private ?EditCommentViewModel      $editCommentForm      = null;
    private ?ReplyCommentViewModel     $replyCommentForm     = null;
    private ?EditCommentReplyViewModel $editReplyCommentForm = null;
    private ?HighlightedFile           $highlightedFile      = null;

    public function __construct(public readonly ?DiffFile $selectedFile)
    {
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

    public function getHighlightedFile(): ?HighlightedFile
    {
        return $this->highlightedFile;
    }

    public function setHighlightedFile(?HighlightedFile $highlightedFile): void
    {
        $this->highlightedFile = $highlightedFile;
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
