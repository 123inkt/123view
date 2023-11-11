<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Utils\Assert;

/**
 * @implements ProcessorInterface<CodeReview>
 */
class CodeReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewEventService $eventService,
        private readonly User $user
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        Assert::isInstanceOf($data, CodeReview::class);
        $data->setUpdateTimestamp(time());

        // save changes
        $this->reviewRepository->save($data, true);

        // dispatch events if state was changed
        if ($data->isPropertyChanged(CodeReview::PROP_STATE)) {
            $originalState = Assert::string($data->getOriginalValue(CodeReview::PROP_STATE));
            $this->eventService->reviewStateChanged($data, $originalState, $this->user->getId());
        }

        return $data;
    }
}
