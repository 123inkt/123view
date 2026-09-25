<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent;

use ArrayIterator;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Traversable;

#[CoversClass(RemoteEventHandler::class)]
class RemoteEventHandlerTest extends AbstractTestCase
{
    /** @var RemoteEventHandlerInterface<PushEvent|MergeRequestEvent>&MockObject */
    private RemoteEventHandlerInterface&MockObject $handler;
    private RemoteEventHandler                     $eventHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->createMock(RemoteEventHandlerInterface::class);

        /** @var Traversable<class-string<PushEvent>|class-string<MergeRequestEvent>, RemoteEventHandlerInterface<PushEvent|MergeRequestEvent>> $iterator */
        $iterator           = new ArrayIterator([PushEvent::class => $this->handler]);
        $this->eventHandler = new RemoteEventHandler($iterator);
    }

    public function testHandle(): void
    {
        $object = new PushEvent();

        $this->handler->expects($this->once())->method('handle')->with($object);
        $this->eventHandler->handle($object);
    }

    public function testHandleUnknownObject(): void
    {
        $this->handler->expects($this->never())->method('handle');
        $this->eventHandler->handle($this);
    }
}
