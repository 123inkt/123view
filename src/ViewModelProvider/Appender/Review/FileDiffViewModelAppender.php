<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\ReviewDto;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use Throwable;

readonly class FileDiffViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private FileDiffViewModelProvider $fileDiffViewModelProvider)
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts(ReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->selectedFile !== null;
    }

    /**
     * @throws Throwable
     */
    public function append(ReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $fileDiffViewModel = $this->fileDiffViewModelProvider->getFileDiffViewModel(
            $dto->review,
            $dto->selectedFile,
            $dto->action,
            $dto->comparePolicy,
            $dto->selectedFile->isModified() ? $dto->diffMode : ReviewDiffModeEnum::INLINE
        );
        $viewModel->setFileDiffViewModel($fileDiffViewModel->setRevisions($dto->visibleRevisions));
        $viewModel->setDescriptionVisible(false);
    }
}
