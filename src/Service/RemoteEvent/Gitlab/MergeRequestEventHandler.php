<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @implements RemoteEventHandlerInterface<MergeRequestEvent>
 */
class MergeRequestEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, MergeRequestEvent::class);
        if ($event->action !== 'approved') {
            return;
        }

        $this->logger?->info('MergeRequestEventHandler: merge request {id} was approved', ['id' => $event->iid]);
    }
}
