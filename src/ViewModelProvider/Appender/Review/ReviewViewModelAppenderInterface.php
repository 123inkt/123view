<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\ReviewAppenderDTO;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

interface ReviewViewModelAppenderInterface
{
    public function accepts(ReviewAppenderDTO $dto, ReviewViewModel $viewModel): bool;

    public function append(ReviewAppenderDTO $dto, ReviewViewModel $viewModel): void;
}
