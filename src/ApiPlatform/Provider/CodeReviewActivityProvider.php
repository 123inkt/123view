<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\Entity\Review\CodeReviewActivity;

class CodeReviewActivityProvider implements ProviderInterface
{
    public function __construct(private readonly CollectionProvider $collectionProvider)
    {
    }

    /**
     * @inheritDoc
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var CodeReviewActivity[] $activities */
        $activities = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $results    = [];

        foreach ($activities as $activity) {
            $results[] = new CodeReviewActivityOutput(
                (int)$activity->getId(),
                (int)$activity->getUser()?->getId(),
                (int)$activity->getReview()?->getId(),
                (string)$activity->getEventName(),
                $activity->getData(),
                (int)$activity->getCreateTimestamp()
            );
        }

        return $results;
    }
}
