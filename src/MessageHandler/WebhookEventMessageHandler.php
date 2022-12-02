<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Service\Webhook\WebhookNotifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async_messages')]
class WebhookEventMessageHandler
{
    public function __construct(private readonly WebhookNotifier $webhookNotifier)
    {
    }

    public function __invoke(CodeReviewAwareInterface $event): void
    {
        $this->webhookNotifier->notify($event);
    }
}
