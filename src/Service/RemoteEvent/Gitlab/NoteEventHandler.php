<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Api\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repository,
        private readonly MessageBusInterface $bus,
        private readonly GitlabApi $api,
        private readonly UserRepository $userRepository,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly BranchRevisionService $branchRevisionService,
    ) {
    }

    /**
     * @phpstan-param NoteEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);

        // find gitlab user
        $gitlabUser = $this->api->users()->getUser($event->userId);
        if ($gitlabUser === null) {
            $this->logger?->notice('NoteEventHandler: user {id} not found in gitlab', ['id' => $event->userId]);

            return;
        }

        // find user
        $user = $this->userRepository->findOneBy(['email' => $gitlabUser->email]);
        if ($user === null) {
            $this->logger?->notice('NoteEventHandler: user {email} not found in 123view', ['email' => $gitlabUser->email]);

            return;
        }

        // find repository
        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null || $repository->isActive() === false) {
            $this->logger?->notice('NoteEventHandler: repository {id} doesnt exist or is inactive in 123view', ['id' => $event->projectId]);

            return;
        }

        // find revisions
        $revisions = $this->branchRevisionService->getRevisionsFor($repository, $event->sourceBranch, $event->targetBranch);
        if (count($revisions) === 0) {
            $this->logger?->notice('NoteEventHandler: no revisions found for branch {name}', ['name' => $event->sourceBranch]);

            return;
        }

        $this->logger->info(print_r($event, true));
    }
}
