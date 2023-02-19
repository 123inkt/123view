<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\User\UserService;
use DR\Review\Utility\Assert;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @implements ProviderInterface<CodeReviewOutput>
 */
class CodeReviewProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $collectionProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserService $userService
    ) {
    }

    /**
     * @inheritDoc
     * @return CodeReviewOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if ($operation instanceof GetCollection === false) {
            throw new InvalidArgumentException('Only GetCollection operation is supported');
        }

        /** @var CodeReview[] $reviews */
        $reviews = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $results = [];

        foreach ($reviews as $review) {
            $reviewers = [];
            foreach ($review->getReviewers() as $reviewer) {
                $user        = Assert::notNull($reviewer->getUser());
                $reviewers[] = new UserOutput((int)$user->getId(), (string)$user->getName(), (string)$user->getEmail());
            }

            $authors = [];
            foreach ($this->userService->getUsersForRevisions($review->getRevisions()->toArray()) as $user) {
                $authors[] = new UserOutput((int)$user->getId(), (string)$user->getName(), (string)$user->getEmail());
            }

            $results[] = new CodeReviewOutput(
                (int)$review->getId(),
                (int)$review->getRepository()?->getId(),
                'cr-' . $review->getProjectId(),
                (string)$review->getTitle(),
                (string)$review->getDescription(),
                $this->urlGenerator->generate(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL),
                (string)$review->getState(),
                $review->getReviewersState(),
                $authors,
                $reviewers,
                (int)$review->getCreateTimestamp(),
                (int)$review->getUpdateTimestamp(),
            );
        }

        return $results;
    }
}
