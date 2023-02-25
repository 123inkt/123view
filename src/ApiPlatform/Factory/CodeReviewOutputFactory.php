<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use ApiPlatform\Api\UrlGeneratorInterface;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Utility\Assert;
use Symfony\Component\Routing\Generator\UrlGenerator;

class CodeReviewOutputFactory
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, private readonly UserOutputFactory $userOutputFactory)
    {
    }

    /**
     * @param CodeReviewer[] $reviewers
     * @param User[]         $authors
     */
    public function create(CodeReview $review, ?array $reviewers = null, ?array $authors = null): CodeReviewOutput
    {
        $reviewersOutput = null;
        $authorOutput    = null;

        if ($reviewers !== null) {
            $reviewersOutput = array_map(fn($reviewer) => $this->userOutputFactory->create(Assert::notNull($reviewer->getUser())), $reviewers);
        }
        if ($authors !== null) {
            $authorOutput = array_map(fn($user) => $this->userOutputFactory->create($user), $authors);
        }

        return new CodeReviewOutput(
            (int)$review->getId(),
            (int)$review->getRepository()?->getId(),
            'cr-' . $review->getProjectId(),
            (string)$review->getTitle(),
            (string)$review->getDescription(),
            $this->urlGenerator->generate(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL),
            (string)$review->getState(),
            $review->getReviewersState(),
            $reviewersOutput,
            $authorOutput,
            (int)$review->getCreateTimestamp(),
            (int)$review->getUpdateTimestamp(),
        );
    }
}
