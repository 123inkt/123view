<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\User\GitlabUserService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<MergeRequestEvent>
 */
class ApprovedMergeRequestEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly GitlabApi $api,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly GitlabUserService $userService
    ) {
    }

    /**
     * @phpstan-param MergeRequestEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, MergeRequestEvent::class);
        if ($event->action !== 'approved') {
            return;
        }

        $repository = $this->repositoryRepository->findByProperty('gitlab-project-id', (string)$event->project->id);
        if ($repository === null) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: no repository found for project id {id}', ['id' => $event->project->id]);

            return;
        }

        if ($repository->isActive() === false) {
            $this->logger?->info('ApprovedMergeRequestEventHandler: repository {name} is not active', ['name' => $repository->getName()]);

            return;
        }

        $user = $this->userService->getUser($event->user->id, $event->user->username);

        $this->logger?->info('ApprovedMergeRequestEventHandler: for repository {name}', ['name' => $repository->getName()]);

        $this->logger?->info('ApprovedMergeRequestEventHandler: merge request {id} was approved', ['id' => $event->iid]);
    }
}
