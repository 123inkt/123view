<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Stringable;
use Traversable;

/**
 * @phpstan-type HandlerValue RemoteEventHandlerInterface<PushEvent|NoteEvent|MergeRequestEvent>
 */
class RemoteEventHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param Traversable<int, HandlerValue> $handlers
     */
    public function __construct(private readonly Traversable $handlers)
    {
    }

    public function handle(object $object): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($object)) {
                $this->logger?->info(
                    'RemoteEventHandler: handling {handler} event for {class}',
                    ['handler' => get_class($handler), 'class' => get_class($object)]
                );
                $handler->handle($object);

                return;
            }
        }

        $this->logger?->info(
            'RemoteEventHandler: no supported event handler found for {class}',
            ['class' => $object instanceof Stringable ? (string)$object : get_class($object)]
        );
    }
}
