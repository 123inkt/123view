<?php
declare(strict_types=1);

namespace DR\Review\RemoteEventConsumer;

use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Review\Service\RemoteEvent\Gitlab\RemoteEventPayloadDenormalizer;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Utils\Assert;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[AsRemoteEventConsumer(GitlabRemoteEvent::REMOTE_EVENT_TYPE)]
class GitlabRemoteEventConsumer implements ConsumerInterface
{
    public function __construct(private readonly RemoteEventPayloadDenormalizer $denormalizer, private readonly RemoteEventHandler $eventHandler)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function consume(RemoteEvent $event): void
    {
        // ensure we only handle gitlab events
        Assert::isInstanceOf($event, GitlabRemoteEvent::class);

        $gitlabEvent = $this->denormalizer->denormalize($event->getName(), $event->getPayload());
        if ($gitlabEvent !== null) {
            $this->eventHandler->handle($gitlabEvent);
        }
    }
}
