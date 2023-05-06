<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Webhook\WebhookRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WebhookNotifier implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly WebhookRepository $webhookRepository,
        private readonly WebhookExecutionService $executionService,
        private readonly CodeReviewRepository $reviewRepository,
    ) {
    }

    public function notify(CodeReviewAwareInterface $event): void
    {
        $repositoryId = $this->reviewRepository->find($event->getReviewId())?->getRepository()?->getId();
        if ($repositoryId === null) {
            $this->logger?->notice('Event has no review/repository attached: ' . get_class($event));

            return;
        }

        $webhooks = $this->webhookRepository->findByRepositoryId($repositoryId, true);
        foreach ($webhooks as $webhook) {
            $this->logger?->info('Invoking webhook: {webhookId}', ['webhookId' => $webhook->getId()]);
            $this->executionService->execute($webhook, $event);
        }
    }
}
