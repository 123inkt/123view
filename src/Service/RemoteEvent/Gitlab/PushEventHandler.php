<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements RemoteEventHandlerInterface<PushEvent>
 */
class PushEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RepositoryRepository $repository, private readonly MessageBusInterface $bus)
    {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, PushEvent::class);

        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null) {
            $this->logger?->info('PushEventHandler: no repository found for project id {id}', ['id' => $event->projectId]);

            return;
        }

        if ($repository->isActive() === false) {
            $this->logger?->info('PushEventHandler: repository {name} is not active', ['name' => $repository->getName()]);

            return;
        }

        $this->logger?->info('PushEventHandler: fetching new revisions for {name}', ['name' => $repository->getName()]);
        $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));
    }
}
