<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use Throwable;

/**
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileDiffViewModelProvider
{
    public function __construct(
        private readonly CommentViewModelProvider $commentModelProvider,
        private readonly CacheableHighlightedFileService $hfService,
        private readonly UnifiedDiffBundler $bundler,
        private readonly UnifiedDiffEmphasizer $emphasizer,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getFileDiffViewModel(
        CodeReview $review,
        DiffFile $selectedFile,
        ?AbstractReviewAction $reviewAction,
        ReviewDiffModeEnum $diffMode
    ): FileDiffViewModel {
        $viewModel = new FileDiffViewModel($selectedFile, $diffMode);

        // gather comments view model
        $viewModel->setCommentsViewModel($this->commentModelProvider->getCommentsViewModel($review, $selectedFile));

        // create highlighted file
        if ($selectedFile->isDeleted() === false) {
            $highlightedFile = $this->hfService->fromDiffFile(Assert::notNull($review->getRepository()), $selectedFile);
            $viewModel->setHighlightedFile($highlightedFile);
        }

        // apply diff mode
        if ($diffMode === ReviewDiffModeEnum::INLINE) {
            $this->bundler->bundleFile($selectedFile);
        } elseif ($diffMode === ReviewDiffModeEnum::UNIFIED) {
            $this->emphasizer->emphasizeFile($selectedFile);
        }

        // setup action forms
        if ($reviewAction instanceof AddCommentReplyAction) {
            $viewModel->setReplyCommentForm($this->commentModelProvider->getReplyCommentViewModel($reviewAction));
        } elseif ($reviewAction instanceof EditCommentReplyAction) {
            $viewModel->setEditReplyCommentForm($this->commentModelProvider->getEditCommentReplyViewModel($reviewAction));
        }

        return $viewModel;
    }
}
