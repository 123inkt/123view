<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\ReviewDtoProvider;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\ReviewViewModelAppenderInterface;
use Throwable;
use Traversable;

readonly class ReviewViewModelProvider
{
    /**
     * @param Traversable<ReviewViewModelAppenderInterface> $reviewViewModelAppenders
     */
    public function __construct(private ReviewDtoProvider $reviewDtoProvider, private Traversable $reviewViewModelAppenders)
    {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review, ReviewRequest $request): ReviewViewModel
    {
        $dto = $this->reviewDtoProvider->provide($review, $request);

        // create view model
        $viewModel = new ReviewViewModel($review, $dto->revisions, $request->getTab(), count($dto->visibleRevisions));

        // append optional view models
        /** @var ReviewViewModelAppenderInterface $viewModelAppender */
        foreach ($this->reviewViewModelAppenders as $viewModelAppender) {
            if ($viewModelAppender->accepts($dto, $viewModel)) {
                $viewModelAppender->append($dto, $viewModel);
            }
        }

        return $viewModel;
    }
}
