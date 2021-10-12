<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use Symfony\Component\Mailer\Event\MessageEvent;

class MessageEventCollector
{
    /** @var MessageEvent[] */
    private array $events;

    public function onMessage(MessageEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return MessageEvent[]
     */
    public function getMessageEvents(): array
    {
        return $this->events;
    }
}
