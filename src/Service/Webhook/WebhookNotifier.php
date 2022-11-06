<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Webhook\WebhookRepository;
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

    public function notify(WebhookEventInterface $event): void
    {
        $repositoryId = $this->reviewRepository->find($event->getReviewId())?->getRepository()?->getId();
        if ($repositoryId === null) {
            $this->logger?->notice('Event has no review/repository attached: ' . get_class($event));

            return;
        }

        $webhooks = $this->webhookRepository->findBy(['enabled' => 1, 'repository' => $repositoryId]);
        foreach ($webhooks as $webhook) {
            $this->logger?->info('Invoking webhook: {webhookId}', ['webhookId' => $webhook->getId()]);
            $this->executionService->execute($webhook, $event);
        }
    }
}
