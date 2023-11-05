<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Traversable;

/**
 * @phpstan-type HandlerKey class-string<PushEvent>
 * @phpstan-type HandlerValue WebhookEventHandlerInterface<PushEvent>
 */
class RemoteEventHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array<HandlerKey, HandlerValue> */
    private array $handlers;

    /**
     * @param Traversable<HandlerKey, HandlerValue> $handlers
     */
    public function __construct(Traversable $handlers)
    {
        $this->handlers = iterator_to_array($handlers);
    }

    public function handle(object $object): void
    {
        $class = get_class($object);
        if (isset($this->handlers[$class]) === false) {
            $this->logger?->info('RemoteEventHandler: no event handler for {class}', ['class' => $class]);

            return;
        }

        $this->logger?->info('RemoteEventHandler: handling event for {class}', ['class' => $class]);

        /** @phpstan-var PushEvent $object */
        $this->handlers[$class]->handle($object);
    }
}
