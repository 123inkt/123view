<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Webhook\DeleteWebhookController;
use DR\Review\Controller\App\Admin\Webhook\WebhooksController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<DeleteWebhookController>
 */
#[CoversClass(DeleteWebhookController::class)]
class DeleteWebhookControllerTest extends AbstractControllerTestCase
{
    private WebhookRepository&MockObject $webhookRepository;

    protected function setUp(): void
    {
        $this->webhookRepository = $this->createMock(WebhookRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $webhook = new Webhook();

        $this->webhookRepository->expects(self::once())->method('remove')->with($webhook, true);
        $this->expectAddFlash('success', 'webhook.successful.removed');
        $this->expectRefererRedirect(WebhooksController::class);

        ($this->controller)($webhook);
    }

    public function getController(): AbstractController
    {
        return new DeleteWebhookController($this->webhookRepository);
    }
}
