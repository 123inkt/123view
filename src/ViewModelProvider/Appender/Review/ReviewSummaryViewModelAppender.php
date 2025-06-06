<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\ReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\ReviewSummaryViewModelProvider;

readonly class ReviewSummaryViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private readonly ReviewSummaryViewModelProvider $summaryViewModelProvider)
    {
    }

    public function accepts(ReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->selectedFile === null;
    }

    public function append(ReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $summaryViewModel = $this->summaryViewModelProvider->getSummaryViewModel($dto->review, $dto->revisions, $dto->fileTree);

        $viewModel->setReviewSummaryViewModel($summaryViewModel);
        $viewModel->setDescriptionVisible(true);
    }
}
