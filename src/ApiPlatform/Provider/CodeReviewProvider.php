<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\User\UserService;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CodeReviewOutput>
 */
class CodeReviewProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<CodeReview> $collectionProvider
     */
    public function __construct(
        private readonly ProviderInterface $collectionProvider,
        private readonly CodeReviewOutputFactory $reviewOutputFactory,
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
            $results[] = $this->reviewOutputFactory->create(
                $review,
                $review->getReviewers()->toArray(),
                $this->userService->getUsersForRevisions($review->getRevisions()->toArray())
            );
        }

        return $results;
    }
}
