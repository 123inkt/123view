<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Utils\Assert;
use Throwable;

class FileReviewViewModelProvider
{
    public function __construct(
        private readonly FileDiffViewModelProvider $fileDiffViewModelProvider,
        private readonly CodeReviewFileService $fileService,
        private readonly CodeReviewTypeDecider $reviewTypeDecider,
        private readonly CodeReviewRevisionService $revisionService,
        private readonly RevisionVisibilityService $visibilityService,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(
        CodeReview $review,
        string $filepath,
        DiffComparePolicy $comparePolicy,
        ReviewDiffModeEnum $diffMode,
        int $visibleLines
    ): FileDiffViewModel {
        $revisions = $this->revisionService->getRevisions($review);
        $visibleRevisions = $this->visibilityService->getVisibleRevisions($review, $revisions);

        // get diff files for review
        $reviewType = $this->reviewTypeDecider->decide($review, $revisions, $visibleRevisions);
        [, $selectedFile] = $this->fileService->getFiles(
            $review,
            $visibleRevisions,
            $filepath,
            new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, $comparePolicy, $reviewType, $visibleLines)
        );

        $viewModel = $this->fileDiffViewModelProvider->getFileDiffViewModel(
            $review,
            Assert::notNull($selectedFile),
            null,
            $comparePolicy,
            $diffMode,
            $visibleLines
        );
        $viewModel->setRevisions($visibleRevisions);

        return $viewModel;
    }
}
