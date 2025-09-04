<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\FileDiffViewModelProvider;
use DR\Utils\Assert;
use Throwable;

readonly class FileDiffViewModelAppender implements ReviewViewModelAppenderInterface
{
    public function __construct(private FileDiffViewModelProvider $fileDiffViewModelProvider)
    {
    }

    /**
     * @inheritDoc
     */
    public function accepts(CodeReviewDto $dto, ReviewViewModel $viewModel): bool
    {
        return $dto->selectedFile !== null;
    }

    /**
     * @throws Throwable
     */
    public function append(CodeReviewDto $dto, ReviewViewModel $viewModel): void
    {
        $fileDiffViewModel = $this->fileDiffViewModelProvider->getFileDiffViewModel(
            $dto->review,
            Assert::notNull($dto->selectedFile),
            $dto->action,
            $dto->comparePolicy,
            Assert::notNull($dto->selectedFile)->isModified() ? $dto->diffMode : ReviewDiffModeEnum::INLINE,
            $dto->visibleLines
        );
        $viewModel->setFileDiffViewModel($fileDiffViewModel->setRevisions($dto->visibleRevisions));
        $viewModel->setDescriptionVisible(false);
    }
}
