<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\MessageHandler\WebhookEventMessageHandler;
use DR\Review\Service\Webhook\WebhookNotifier;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(WebhookEventMessageHandler::class)]
class WebhookEventMessageHandlerTest extends AbstractTestCase
{
    private WebhookNotifier&MockObject $notifier;
    private WebhookEventMessageHandler $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->notifier = $this->createMock(WebhookNotifier::class);
        $this->handler  = new WebhookEventMessageHandler($this->notifier);
    }

    public function testInvoke(): void
    {
        $event = $this->createMock(CodeReviewAwareInterface::class);
        $this->notifier->expects($this->once())->method('notify')->with($event);
        ($this->handler)($event);
    }
}
