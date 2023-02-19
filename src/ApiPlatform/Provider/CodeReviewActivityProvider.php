<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\Entity\Review\CodeReviewActivity;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CodeReviewActivityOutput>
 */
class CodeReviewActivityProvider implements ProviderInterface
{
    public function __construct(private readonly ProviderInterface $collectionProvider)
    {
    }

    /**
     * @inheritDoc
     * @return CodeReviewActivityOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if ($operation instanceof GetCollection === false) {
            throw new InvalidArgumentException('Only GetCollection operation is supported');
        }

        /** @var CodeReviewActivity[] $activities */
        $activities = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $results    = [];

        foreach ($activities as $activity) {
            $results[] = new CodeReviewActivityOutput(
                (int)$activity->getId(),
                (int)$activity->getUser()?->getId(),
                (int)$activity->getReview()?->getId(),
                (string)$activity->getEventName(),
                array_filter($activity->getData(), static fn($val) => $val !== null),
                (int)$activity->getCreateTimestamp()
            );
        }

        return $results;
    }
}
