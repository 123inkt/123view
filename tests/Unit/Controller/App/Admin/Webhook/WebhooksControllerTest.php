<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\Webhook\WebhooksController;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\WebhooksViewModel;
use DR\Review\ViewModelProvider\WebhooksViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<WebhooksController>
 */
#[CoversClass(WebhooksController::class)]
class WebhooksControllerTest extends AbstractControllerTestCase
{
    private WebhooksViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(WebhooksViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $viewModel = $this->createMock(WebhooksViewModel::class);

        $this->viewModelProvider->expects(self::once())->method('getWebhooksViewModel')->willReturn($viewModel);

        static::assertSame(['webhooksViewModel' => $viewModel], ($this->controller)());
    }

    public function getController(): AbstractController
    {
        return new WebhooksController($this->viewModelProvider);
    }
}
