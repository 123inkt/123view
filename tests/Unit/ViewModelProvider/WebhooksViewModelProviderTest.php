<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\WebhooksViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(WebhooksViewModelProvider::class)]
class WebhooksViewModelProviderTest extends AbstractTestCase
{
    private WebhookRepository&MockObject $webhookRepository;
    private WebhooksViewModelProvider    $viewModelProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhookRepository = $this->createMock(WebhookRepository::class);
        $this->viewModelProvider = new WebhooksViewModelProvider($this->webhookRepository);
    }

    public function testGetWebhooksViewModel(): void
    {
        $webhook = new Webhook();

        $this->webhookRepository->expects($this->once())->method('findBy')->with([], ['id' => 'ASC'])->willReturn([$webhook]);

        $viewModel = $this->viewModelProvider->getWebhooksViewModel();
        static::assertSame([$webhook], $viewModel->webhooks);
    }
}
