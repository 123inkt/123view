<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Webhook\Webhook;

class WebhooksViewModel
{
    /**
     * @codeCoverageIgnore
     *
     * @param Webhook[] $webhooks
     */
    public function __construct(public readonly array $webhooks)
    {
    }
}
