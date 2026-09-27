<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\Entity\Review\CodeReview;
use DR\Utils\Arrays;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * @implements ProviderInterface<CodeReviewOutput>
 */
class CodeReviewProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<CodeReview> $collectionProvider
     * @param ProviderInterface<CodeReview> $itemProvider
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private readonly ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private readonly ProviderInterface $itemProvider,
        private readonly CodeReviewOutputFactory $reviewOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     * @return CodeReviewOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|CodeReviewOutput|null
    {
        if ($operation instanceof GetCollection) {
            return $this->provideCollection($operation, $uriVariables, $context);
        }
        if ($operation instanceof Get) {
            return $this->provideItem($operation, $uriVariables, $context);
        }
        throw new InvalidArgumentException('Only GetCollection or Get operation is supported');
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @return CodeReviewOutput[]
     * @throws Throwable
     */
    private function provideCollection(Operation $operation, array $uriVariables, array $context): array
    {
        /** @var iterable<int, CodeReview> $reviews */
        $reviews = $this->collectionProvider->provide($operation, $uriVariables, $context);

        return Arrays::map($reviews, fn(CodeReview $review) => $this->reviewOutputFactory->create($review));
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws Throwable
     */
    private function provideItem(Operation $operation, array $uriVariables, array $context): ?CodeReviewOutput
    {
        /** @var CodeReview|null $review */
        $review = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $review === null ? null : $this->reviewOutputFactory->create($review);
    }
}
