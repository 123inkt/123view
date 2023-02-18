<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\User\UserService;
use Symfony\Component\Routing\Generator\UrlGenerator;

class CodeReviewProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserService $userService
    ) {
    }

    /**
     * @inheritDoc
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var CodeReview[] $reviews */
        $reviews = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $results = [];

        foreach ($reviews as $review) {
            $reviewers = [];
            foreach ($review->getReviewers() as $reviewer) {
                $reviewers[] = new UserOutput((int)$reviewer->getUser()?->getId(), (string)$reviewer->getUser()?->getEmail());
            }

            $authors = [];
            foreach ($this->userService->getUsersForRevisions($review->getRevisions()->toArray()) as $user) {
                $authors[] = new UserOutput((int)$user->getId(), (string)$user->getEmail());
            }

            $results[] = new CodeReviewOutput(
                $review->getId(),
                (int)$review->getRepository()?->getId(),
                'cr-' . $review->getProjectId(),
                $review->getTitle(),
                $review->getDescription(),
                $this->urlGenerator->generate(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL),
                $review->getState(),
                $review->getReviewersState(),
                $authors,
                $reviewers,
                $review->getCreateTimestamp(),
                $review->getUpdateTimestamp(),
            );
        }

        return $results;
    }
}
