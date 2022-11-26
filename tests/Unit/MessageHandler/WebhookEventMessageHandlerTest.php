<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\MessageHandler\WebhookEventMessageHandler;
use DR\GitCommitNotification\Service\Webhook\WebhookNotifier;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\WebhookEventMessageHandler
 * @covers ::__construct
 */
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

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $event = $this->createMock(WebhookEventInterface::class);
        $this->notifier->expects(self::once())->method('notify')->with($event);
        ($this->handler)($event);
    }
}
