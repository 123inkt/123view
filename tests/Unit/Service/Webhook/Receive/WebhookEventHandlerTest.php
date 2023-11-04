<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook\Receive;

use ArrayIterator;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\Webhook\Receive\WebhookEventHandler;
use DR\Review\Service\Webhook\Receive\WebhookEventHandlerInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

#[CoversClass(WebhookEventHandler::class)]
class WebhookEventHandlerTest extends AbstractTestCase
{
    /** @var WebhookEventHandlerInterface<PushEvent>&MockObject */
    private WebhookEventHandlerInterface&MockObject $handler;
    private WebhookEventHandler                     $eventHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler      = $this->createMock(WebhookEventHandlerInterface::class);
        $this->eventHandler = new WebhookEventHandler(new ArrayIterator([stdClass::class => $this->handler]));
    }

    public function testHandle(): void
    {
        $object = new stdClass();

        $this->handler->expects(self::once())->method('handle')->with($object);
        $this->eventHandler->handle($object);
    }

    public function testHandleUnknownObject(): void
    {
        $this->handler->expects(self::never())->method('handle');
        $this->eventHandler->handle($this);
    }
}
