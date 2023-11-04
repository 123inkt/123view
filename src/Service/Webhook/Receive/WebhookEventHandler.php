<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Traversable;

class WebhookEventHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array<string, WebhookEventHandlerInterface<PushEvent>> */
    private array $handlers;

    /**
     * @param Traversable<string, WebhookEventHandlerInterface<PushEvent>> $handlers
     */
    public function __construct(Traversable $handlers)
    {
        $this->handlers = iterator_to_array($handlers);
    }

    public function handle(object $object): void
    {
        $class = get_class($object);
        if (isset($this->handlers[$class]) === false) {
            $this->logger?->info('WebhookEventHandler: no event handler for {class}', ['class' => $class]);

            return;
        }

        $this->logger?->info('WebhookEventHandler: handling event for {class}', ['class' => $class]);

        /** @phpstan-var PushEvent $object */
        $this->handlers[$class]->handle($object);
    }
}
