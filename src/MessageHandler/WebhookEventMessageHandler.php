<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Service\Webhook\WebhookNotifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class WebhookEventMessageHandler
{
    public function __construct(private readonly WebhookNotifier $webhookNotifier)
    {
    }

    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(CodeReviewAwareInterface $event): void
    {
        $this->webhookNotifier->notify($event);
    }
}
