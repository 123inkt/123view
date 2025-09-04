<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\Review\Service\Git\Diff\UnifiedDiffBundler;
use DR\Review\Service\Git\Diff\UnifiedDiffEmphasizer;
use DR\Review\Service\Git\Diff\UnifiedDiffSplitter;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\HighlightFileViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Utils\Assert;
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
        private readonly CodeQualityViewModelProvider $codeQualityViewModelProvider,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getFileDiffViewModel(
        CodeReview $review,
        DiffFile $selectedFile,
        ?AbstractReviewAction $reviewAction,
        DiffComparePolicy $comparePolicy,
        ReviewDiffModeEnum $diffMode,
        int $visibleLines
    ): FileDiffViewModel {
        $viewModel = new FileDiffViewModel($selectedFile, $diffMode, $visibleLines);

        // create highlighted file
        if ($selectedFile->isDeleted() === false) {
            $highlightedFile = $this->hfService->fromDiffFile(Assert::notNull($review->getRepository()), $selectedFile);
            $viewModel->setHighlightedFileViewModel(new HighlightFileViewModel($highlightedFile));
        }

        // apply diff mode
        if ($diffMode === ReviewDiffModeEnum::INLINE) {
            // inline diff + trim|ignore whitespace is difficult to keep newlines consistent. Default to standard compare policy
            $this->bundler->bundleFile($selectedFile, DiffComparePolicy::ALL);
        } elseif ($diffMode === ReviewDiffModeEnum::UNIFIED) {
            $this->emphasizer->emphasizeFile($selectedFile, $comparePolicy);
        } elseif ($diffMode === ReviewDiffModeEnum::SIDE_BY_SIDE) {
            $this->emphasizer->emphasizeFile($selectedFile, $comparePolicy);
            $viewModel->leftSideFile = $this->splitter->splitFile($selectedFile);
        }

        // gather comments view model
        $viewModel->setCommentsViewModel($this->commentsModelProvider->getCommentsViewModel($review, $viewModel->leftSideFile, $selectedFile));

        // setup action form
        if ($reviewAction instanceof AddCommentReplyAction) {
            $viewModel->setReplyCommentForm($this->commentModelProvider->getReplyCommentViewModel($reviewAction));
        }

        // gather code inspection issues
        $viewModel->setCodeQualityViewModel($this->codeQualityViewModelProvider->getCodeQualityViewModel($review, $selectedFile->getPathname()));

        return $viewModel;
    }
}
