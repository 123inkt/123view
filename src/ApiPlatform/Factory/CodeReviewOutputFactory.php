<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\User\UserService;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CodeReviewOutputFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserOutputFactory $userOutputFactory,
        private readonly CodeReviewerStateResolver $reviewerStateResolver,
        private readonly CodeReviewRevisionService $reviewRevisionService,
        private readonly UserService $userService,
    ) {
    }

    public function create(CodeReview $review): CodeReviewOutput
    {
        $reviewers       = $review->getReviewers();
        $revisions       = $this->reviewRevisionService->getRevisions($review);
        $reviewersOutput = null;
        $authorOutput    = null;
        $hashes          = null;

        if (count($reviewers) > 0) {
            $reviewersOutput = Arrays::map($reviewers, fn($reviewer) => $this->userOutputFactory->create(Assert::notNull($reviewer->getUser())));
        }
        if (count($revisions) > 0) {
            $authors      = $this->userService->getUsersForRevisions($revisions);
            $authorOutput = array_map(fn($user) => $this->userOutputFactory->create($user), $authors);
        }
        if (count($revisions) > 0) {
            $hashes = ['startSha' => Arrays::first($revisions)->getCommitHash(), 'endSha' => Arrays::last($revisions)->getCommitHash()];
        }

        return new CodeReviewOutput(
            $review->getId(),
            $review->getRepository()->getId(),
            'cr-' . $review->getProjectId(),
            $review->getTitle(),
            $review->getDescription(),
            $this->urlGenerator->generate(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL),
            $review->getType(),
            (string)$review->getState(),
            $this->reviewerStateResolver->getReviewersState($review),
            $hashes,
            $authorOutput,
            $reviewersOutput,
            $review->getCreateTimestamp(),
            $review->getUpdateTimestamp(),
        );
    }
}
