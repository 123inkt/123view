<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive;

use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Traversable;

class WebhookEventHandler
{
    /** @var array<string, WebhookEventHandlerInterface<NoteEvent|PushEvent>> */
    private array $handlers;

    /**
     * @param Traversable<string, WebhookEventHandlerInterface<NoteEvent|PushEvent>> $handlers
     */
    public function __construct(Traversable $handlers)
    {
        $this->handlers = iterator_to_array($handlers);
    }

    /**
     * @phpstan-param NoteEvent|PushEvent $event
     */
    public function handle(object $event): void
    {
        $class = get_class($event);
        if (isset($this->handlers[$class]) === false) {
            return;
        }

        $this->handlers[$class]->handle($event);
    }
}
