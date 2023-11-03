<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive;

/**
 * @template T of object
 */
interface WebhookEventHandlerInterface
{
    /**
     * @param T $event
     */
    public function handle(object $event): void;
}
