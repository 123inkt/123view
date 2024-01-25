<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements RemoteEventHandlerInterface<MergeRequestEvent>
 */
class ApprovedMergeRequestEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RepositoryRepository $repository, private readonly MessageBusInterface $bus)
    {
    }

    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, MergeRequestEvent::class);
        if ($event->action !== 'approved') {
            return;
        }

        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->project->id);
        if ($repository === null) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: no repository found for project id {id}', ['id' => $event->project->id]);

            return;
        }

        if ($repository->isActive() === false) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: repository {name} is not active', ['name' => $repository->getName()]);

            return;
        }

        $this->logger?->info('ApprovedMergeRequestEventHandler: merge request {id} was approved', ['id' => $event->iid]);
    }
}
