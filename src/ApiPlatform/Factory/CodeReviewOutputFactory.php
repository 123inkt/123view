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
    public function create(CodeReview $review, array $reviewers = [], array $authors = []): CodeReviewOutput
    {
        $reviewersOutput = [];
        foreach ($reviewers as $reviewer) {
            $reviewersOutput[] = $this->userOutputFactory->create(Assert::notNull($reviewer->getUser()));
        }

        $authorOutput = [];
        foreach ($authors ?? [] as $user) {
            $authorOutput[] = $this->userOutputFactory->create($user);
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
