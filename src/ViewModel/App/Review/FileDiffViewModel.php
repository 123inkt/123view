<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\ViewModel\App\Comment\CommentsViewModel;
use DR\Review\ViewModel\App\Comment\EditCommentReplyViewModel;
use DR\Review\ViewModel\App\Comment\EditCommentViewModel;
use DR\Review\ViewModel\App\Comment\ReplyCommentViewModel;

class FileDiffViewModel
{
    private ?CommentsViewModel         $commentsViewModel    = null;
    private ?EditCommentViewModel      $editCommentForm      = null;
    private ?ReplyCommentViewModel     $replyCommentForm     = null;
    private ?EditCommentReplyViewModel $editReplyCommentForm = null;
    private ?HighlightedFile           $highlightedFile      = null;

    public function __construct(public readonly ?DiffFile $selectedFile, public readonly ReviewDiffModeEnum $diffMode)
    {
    }

    /**
     * @return string[]
     */
    public function getDiffModes(): array
    {
        return ReviewDiffModeEnum::values();
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

    public function setHighlightedFile(HighlightedFile $highlightedFile): void
    {
        $this->highlightedFile = $highlightedFile;
    }
}
