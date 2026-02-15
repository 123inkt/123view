<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\RemoteEventConsumer;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Review\RemoteEventConsumer\GitlabRemoteEventConsumer;
use DR\Review\Service\RemoteEvent\Gitlab\RemoteEventPayloadDenormalizer;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[CoversClass(GitlabRemoteEventConsumer::class)]
class GitlabRemoteEventConsumerTest extends AbstractTestCase
{
    private RemoteEventPayloadDenormalizer&MockObject $denormalizer;
    private RemoteEventHandler&MockObject             $eventHandler;
    private GitlabRemoteEventConsumer                 $eventConsumer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->denormalizer  = $this->createMock(RemoteEventPayloadDenormalizer::class);
        $this->eventHandler  = $this->createMock(RemoteEventHandler::class);
        $this->eventConsumer = new GitlabRemoteEventConsumer($this->denormalizer, $this->eventHandler);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testConsumeInvalidEvent(): void
    {
        $this->denormalizer->expects($this->never())->method('denormalize');
        $this->eventHandler->expects($this->never())->method('handle');
        $event = new RemoteEvent('name', 'id', ['payload']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be instance of ' . GitlabRemoteEvent::class);
        $this->eventConsumer->consume($event);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testConsumeUnknownEvent(): void
    {
        $event = new GitlabRemoteEvent('name', 'id', ['payload']);

        $this->denormalizer->expects($this->once())->method('denormalize')->with('name', ['payload'])->willReturn(null);
        $this->eventHandler->expects($this->never())->method('handle');

        $this->eventConsumer->consume($event);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testConsumeKnownEvent(): void
    {
        $event       = new GitlabRemoteEvent('name', 'id', ['payload']);
        $gitlabEvent = new PushEvent();

        $this->denormalizer->expects($this->once())->method('denormalize')->with('name', ['payload'])->willReturn($gitlabEvent);
        $this->eventHandler->expects($this->once())->method('handle')->with($gitlabEvent);

        $this->eventConsumer->consume($event);
    }
}
