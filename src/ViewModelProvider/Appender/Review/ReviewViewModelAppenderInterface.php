<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

interface ReviewViewModelAppenderInterface
{
    public function accepts(CodeReviewDto $dto, ReviewViewModel $viewModel): bool;

    public function append(CodeReviewDto $dto, ReviewViewModel $viewModel): void;
}
