<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\ViewModel\App\Admin\WebhooksViewModel;

class WebhooksViewModelProvider
{
    public function __construct(private readonly WebhookRepository $webhookRepository)
    {
    }

    public function getWebhooksViewModel(): WebhooksViewModel
    {
        $webhooks = $this->webhookRepository->findBy([], ['id' => 'ASC']);

        return new WebhooksViewModel($webhooks);
    }
}
