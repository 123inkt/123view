<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Webhook\WebhookController;
use DR\Review\Controller\App\Admin\Webhook\WebhooksController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Form\Webhook\EditWebhookFormType;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\EditWebhookViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends AbstractControllerTestCase<WebhookController>
 */
#[CoversClass(WebhookController::class)]
class WebhookControllerTest extends AbstractControllerTestCase
{
    private WebhookRepository&MockObject $webhookRepository;

    protected function setUp(): void
    {
        $this->webhookRepository = $this->createMock(WebhookRepository::class);
        parent::setUp();
    }

    public function testInvokeNotFound(): void
    {
        $request = new Request(attributes: ['id' => 5]);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Webhook not found');
        ($this->controller)($request, null);
    }

    public function testInvokeEditWebhook(): void
    {
        $request = new Request();
        $webhook = new Webhook();

        $form = $this->createMock(FormView::class);

        $this->expectCreateForm(EditWebhookFormType::class, ['webhook' => $webhook])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false)
            ->createViewWillReturn($form);

        $result = ($this->controller)($request, $webhook);
        static::assertEquals(['editWebhookModel' => new EditWebhookViewModel($webhook, $form)], $result);
    }

    public function testInvokeFormSubmit(): void
    {
        $request = new Request();
        $webhook = new Webhook();

        $this->expectCreateForm(EditWebhookFormType::class, ['webhook' => $webhook])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->webhookRepository->expects($this->once())->method('save')->with($webhook, true);
        $this->expectAddFlash('success', 'webhook.successful.saved');
        $this->expectRedirectToRoute(WebhooksController::class)->willReturn('url');

        ($this->controller)($request, $webhook);
    }

    public function getController(): AbstractController
    {
        return new WebhookController($this->webhookRepository);
    }
}
