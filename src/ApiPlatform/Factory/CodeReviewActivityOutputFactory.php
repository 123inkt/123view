<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Utils\Assert;

class CodeReviewActivityOutputFactory
{
    public function __construct(private readonly UserOutputFactory $userOutputFactory, private readonly CodeReviewOutputFactory $reviewOutputFactory)
    {
    }

    public function create(CodeReviewActivity $activity): CodeReviewActivityOutput
    {
        $user   = $activity->getUser() === null ? null : $this->userOutputFactory->create($activity->getUser());
        $review = $this->reviewOutputFactory->create(Assert::notNull($activity->getReview()));

        return new CodeReviewActivityOutput(
            (int)$activity->getId(),
            $user,
            $review,
            $activity->getEventName(),
            array_filter($activity->getData(), static fn($val) => $val !== null),
            $activity->getCreateTimestamp()
        );
    }
}
