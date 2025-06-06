<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\ReviewSummaryViewModelProvider;

readonly class ReviewSummaryViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private ReviewSummaryViewModelProvider $summaryViewModelProvider)
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts(CodeReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->selectedFile === null;
    }

    public function append(CodeReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $summaryViewModel = $this->summaryViewModelProvider->getSummaryViewModel($dto->review, $dto->revisions, $dto->fileTree);

        $viewModel->setReviewSummaryViewModel($summaryViewModel);
        $viewModel->setDescriptionVisible(true);
    }
}
