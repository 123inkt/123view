<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Webhook\WebhookRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WebhookNotifier implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly WebhookRepository $webhookRepository, private readonly WebhookExecutionService $executionService)
    {
    }

    public function notify(WebhookEventInterface $event): void
    {
        $webhooks = $this->webhookRepository->findBy(['enabled' => 1]);
        foreach ($webhooks as $webhook) {
            $this->logger?->info('Invoking webhook: {webhookId}', ['webhookId' => $webhook->getId()]);
            $this->executionService->execute($webhook, $event);
        }
    }
}
