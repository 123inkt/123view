<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider\Appender\Review;

use DR\Review\Model\Review\ReviewDto;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

interface ReviewViewModelAppenderInterface
{
    public function accepts(ReviewDto $dto, ReviewViewModel $viewModel): bool;

    public function append(ReviewDto $dto, ReviewViewModel $viewModel): void;
}
