<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive\Gitlab;

use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Service\Webhook\Receive\WebhookEventHandlerInterface;

/**
 * @implements WebhookEventHandlerInterface<NoteEvent>
 */
class NoteEventHandler implements WebhookEventHandlerInterface
{
    public function handle(object $event): void
    {
        $test = true;
    }
}
