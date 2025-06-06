<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use Throwable;

class ReviewViewModelProvider
{
    public function __construct(private readonly CodeReviewRevisionService $revisionService)
    {
    }

    /**
     * @throws Throwable
     */
    public function getViewModel(CodeReview $review): ReviewViewModel
    {
        return new ReviewViewModel($review, $this->revisionService->getRevisions($review));
    }
}
