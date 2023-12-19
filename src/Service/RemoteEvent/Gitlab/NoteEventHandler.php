<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Api\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RepositoryRepository $repository, private readonly MessageBusInterface $bus)
    {
    }

    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);

        $this->logger->info(print_r($event, true));
    }
}
