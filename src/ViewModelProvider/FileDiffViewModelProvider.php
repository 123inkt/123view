<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Service\Git\Diff\UnifiedDiffSplitter;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use Throwable;

class FileDiffViewModelProvider
{
    public function __construct(
        private readonly CommentViewModelProvider $commentModelProvider,
        private readonly CommentsViewModelProvider $commentsModelProvider,
        private readonly CacheableHighlightedFileService $hfService,
        private readonly UnifiedDiffBundler $bundler,
        private readonly UnifiedDiffEmphasizer $emphasizer,
        private readonly UnifiedDiffSplitter $splitter,
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
        } elseif ($diffMode === ReviewDiffModeEnum::SIDE_BY_SIDE) {
            $this->emphasizer->emphasizeFile($selectedFile);
            $viewModel->leftSideFile = $this->splitter->splitFile($selectedFile);
        }

        // gather comments view model
        $viewModel->setCommentsViewModel($this->commentsModelProvider->getCommentsViewModel($review, $viewModel->leftSideFile, $selectedFile));

        // setup action form
        if ($reviewAction instanceof AddCommentReplyAction) {
            $viewModel->setReplyCommentForm($this->commentModelProvider->getReplyCommentViewModel($reviewAction));
        }

        return $viewModel;
    }
}
