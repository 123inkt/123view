<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Review\ReviewAppenderDTO;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\CodeReviewTypeDecider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\ReviewViewModelAppenderInterface;
use Throwable;
use Traversable;

class ReviewViewModelProvider
{
    /**
     * @param Traversable<ReviewViewModelAppenderInterface> $reviewViewModelAppenders
     */
    public function __construct(
        private readonly CodeReviewRevisionService $revisionService,
        private readonly CodeReviewFileService $fileService,
        private readonly CodeReviewTypeDecider $reviewTypeDecider,
        private readonly RevisionVisibilityService $visibilityService,
        private readonly Traversable $reviewViewModelAppenders,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ReviewRequest $request): ReviewViewModel
    {
        $revisions        = $this->revisionService->getRevisions($review);
        $visibleRevisions = $this->visibilityService->getVisibleRevisions($review, $revisions);

        // get diff files for review
        $reviewType = $this->reviewTypeDecider->decide($review, $revisions, $visibleRevisions);
        [$fileTree, $selectedFile] = $this->fileService->getFiles(
            $review,
            $visibleRevisions,
            $request->getFilePath(),
            new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, $request->getComparisonPolicy(), $reviewType)
        );

        // create view model
        $viewModel = new ReviewViewModel($review, $revisions, $request->getTab(), count($visibleRevisions));

        // append optional view models
        $dto = new ReviewAppenderDTO($review, $revisions, $visibleRevisions, $fileTree, $selectedFile, $request);
        /** @var ReviewViewModelAppenderInterface $viewModelAppender */
        foreach ($this->reviewViewModelAppenders as $viewModelAppender) {
            if ($viewModelAppender->accepts($dto, $viewModel)) {
                $viewModelAppender->append($dto, $viewModel);
            }
        }

        return $viewModel;
    }
}
