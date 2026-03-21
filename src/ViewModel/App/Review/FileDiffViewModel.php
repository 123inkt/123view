<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ViewModel\App\Comment\CommentsViewModel;
use DR\Review\ViewModel\App\Comment\ReplyCommentViewModel;
use DR\Utils\Arrays;

class FileDiffViewModel
{
    /** @var DiffFile|null in side-by-side mode the file on the left side */
    public ?DiffFile                $leftSideFile         = null;
    private ?CommentsViewModel      $commentsViewModel    = null;
    private ?ReplyCommentViewModel  $replyCommentForm     = null;
    private ?HighlightFileViewModel $highlightedModel     = null;
    private ?CodeQualityViewModel   $codeQualityViewModel = null;
    /** @var Revision[] */
    private array $revisions = [];

    public function __construct(
        public readonly DiffFile $selectedFile,
        public readonly ReviewDiffModeEnum $diffMode,
        public readonly int $visibleLines
    ) {
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

    public function getHighlightedFileViewModel(): ?HighlightFileViewModel
    {
        return $this->highlightedModel;
    }

    public function setHighlightedFileViewModel(?HighlightFileViewModel $viewModel): void
    {
        $this->highlightedModel = $viewModel;
    }

    public function getCodeQualityViewModel(): ?CodeQualityViewModel
    {
        return $this->codeQualityViewModel;
    }

    public function setCodeQualityViewModel(?CodeQualityViewModel $codeQualityViewModel): void
    {
        $this->codeQualityViewModel = $codeQualityViewModel;
    }

    /**
     * @return Revision[]
     */
    public function getRevisions(): array
    {
        return $this->revisions;
    }

    public function getHeadSha(): ?string
    {
        return Arrays::lastOrNull($this->revisions)?->getCommitHash();
    }

    /**
     * @param Revision[] $revisions
     */
    public function setRevisions(array $revisions): self
    {
        $this->revisions = $revisions;

        return $this;
    }
}
