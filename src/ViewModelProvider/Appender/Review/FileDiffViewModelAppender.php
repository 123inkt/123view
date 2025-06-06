<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\ReviewAppenderDTO;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use Throwable;

readonly class FileDiffViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private FileDiffViewModelProvider $fileDiffViewModelProvider) { }

    public function accepts(ReviewAppenderDTO $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->selectedFile !== null;
    }

    /**
     * @throws Throwable
     */
    public function append(ReviewAppenderDTO $dto, ReviewViewModel $viewModel): void
    {
        $fileDiffViewModel = $this->fileDiffViewModelProvider->getFileDiffViewModel(
            $dto->review,
            $dto->selectedFile,
            $dto->request->getAction(),
            $dto->request->getComparisonPolicy(),
            $dto->selectedFile->isModified() ? $dto->request->getDiffMode() : ReviewDiffModeEnum::INLINE
        );
        $viewModel->setFileDiffViewModel($fileDiffViewModel->setRevisions($dto->visibleRevisions));
        $viewModel->setDescriptionVisible(false);
    }
}
