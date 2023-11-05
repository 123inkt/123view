<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\Webhook\GitlabWebhookController;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\RemoteEvent\Gitlab\RemoteEventPayloadDenormalizer;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(GitlabWebhookController::class)]
class GitlabWebhookControllerTest extends AbstractControllerTestCase
{
    private RemoteEventPayloadDenormalizer&MockObject $deserializer;
    private RemoteEventHandler&MockObject             $eventHandler;

    protected function setUp(): void
    {
        $this->deserializer = $this->createMock(RemoteEventPayloadDenormalizer::class);
        $this->eventHandler = $this->createMock(RemoteEventHandler::class);
        parent::setUp();
    }

    public function testInvokeInvalidRequest(): void
    {
        $request = new Request(server: ['HTTP_X_GITLAB_EVENT' => 'push'], content: 'data');

        $this->deserializer->expects(static::once())->method('deserialize')->with('push', 'data')->willReturn(null);
        $this->eventHandler->expects(static::never())->method('handle');

        static::assertEquals(new Response('OK'), ($this->controller)($request));
    }

    public function testInvokeValidRequest(): void
    {
        $request = new Request(server: ['HTTP_X_GITLAB_EVENT' => 'push'], content: 'data');
        $event   = new PushEvent();

        $this->deserializer->expects(static::once())->method('deserialize')->with('push', 'data')->willReturn($event);
        $this->eventHandler->expects(static::once())->method('handle')->with($event);

        static::assertEquals(new Response('OK'), ($this->controller)($request));
    }

    public function getController(): AbstractController
    {
        return new GitlabWebhookController($this->deserializer, $this->eventHandler);
    }
}
