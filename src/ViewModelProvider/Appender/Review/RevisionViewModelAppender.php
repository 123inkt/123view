<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;

readonly class RevisionViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private RevisionViewModelProvider $revisionModelProvider)
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts(CodeReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $viewModel->getSidebarTabMode() === ReviewViewModel::SIDEBAR_TAB_REVISIONS;
    }

    public function append(CodeReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $viewModel->setRevisionViewModel($this->revisionModelProvider->getRevisionViewModel($dto->review, $dto->revisions));
    }
}
