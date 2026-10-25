<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent;

use ArrayIterator;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use stdClass;
use Traversable;

#[CoversClass(RemoteEventHandler::class)]
class RemoteEventHandlerTest extends AbstractTestCase
{
    /** @var RemoteEventHandlerInterface<PushEvent|NoteEvent|MergeRequestEvent>&MockObject */
    private RemoteEventHandlerInterface&MockObject $handler;
    private LoggerInterface&MockObject             $messageLogger;
    private RemoteEventHandler                     $eventHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->createMock(RemoteEventHandlerInterface::class);
        $this->messageLogger = $this->createMock(LoggerInterface::class);

        /** @var Traversable<int, RemoteEventHandlerInterface<PushEvent|NoteEvent|MergeRequestEvent>> $iterator */
        $iterator           = new ArrayIterator([$this->handler]);
        $this->eventHandler = new RemoteEventHandler($iterator);
        $this->eventHandler->setLogger($this->messageLogger);
    }

    public function testHandle(): void
    {
        $object = new PushEvent();

        $this->handler->expects($this->once())->method('supports')->with($object)->willReturn(true);
        $this->handler->expects($this->once())->method('handle')->with($object);
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'RemoteEventHandler: handling {handler} event for {class}',
                ['handler' => get_class($this->handler), 'class' => PushEvent::class]
            );

        $this->eventHandler->handle($object);
    }

    public function testHandleUnknownObject(): void
    {
        $object = new stdClass();

        $this->handler->expects($this->once())->method('supports')->with($object)->willReturn(false);
        $this->handler->expects($this->never())->method('handle');
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'RemoteEventHandler: no supported event handler found for {class}',
                ['class' => stdClass::class]
            );

        $this->eventHandler->handle($object);
    }
}
