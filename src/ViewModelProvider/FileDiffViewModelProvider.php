<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModelProvider;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;
use Throwable;

/**
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileDiffViewModelProvider
{
    public function __construct(
        private readonly CommentViewModelProvider $commentModelProvider,
        private readonly CacheableHighlightedFileService $highlightedFileService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getFileDiffViewModel(CodeReview $review, ?DiffFile $selectedFile, ?AbstractReviewAction $reviewAction): FileDiffViewModel
    {
        $viewModel = new FileDiffViewModel($selectedFile);

        if ($selectedFile !== null) {
            $viewModel->setCommentsViewModel($this->commentModelProvider->getCommentsViewModel($review, $selectedFile));

            if ($selectedFile->isDeleted() === false) {
                $highlightedFile = $this->highlightedFileService->getHighlightedFile(
                    Assert::notFalse($review->getRevisions()->last()),
                    $selectedFile->getPathname()
                );
                $viewModel->setHighlightedFile($highlightedFile);
            }
        }

        // setup action forms
        if ($selectedFile !== null && $reviewAction instanceof AddCommentAction) {
            $viewModel->setAddCommentForm($this->commentModelProvider->getAddCommentViewModel($review, $selectedFile, $reviewAction));
        } elseif ($reviewAction instanceof EditCommentAction) {
            $viewModel->setEditCommentForm($this->commentModelProvider->getEditCommentViewModel($reviewAction));
        } elseif ($reviewAction instanceof AddCommentReplyAction) {
            $viewModel->setReplyCommentForm($this->commentModelProvider->getReplyCommentViewModel($reviewAction));
        } elseif ($reviewAction instanceof EditCommentReplyAction) {
            $viewModel->setEditReplyCommentForm($this->commentModelProvider->getEditCommentReplyViewModel($reviewAction));
        }

        return $viewModel;
    }
}
