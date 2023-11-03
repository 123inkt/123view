<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\ExternalTool\Gitlab\GitlabApi;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Model\Webhook\Gitlab\Position;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Webhook\Receive\WebhookEventHandlerInterface;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

/**
 * @implements WebhookEventHandlerInterface<NoteEvent>
 */
class NoteEventHandler implements WebhookEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly GitlabApi $api,
        private readonly RepositoryRepository $repository,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);

        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null) {
            $this->logger?->info('NoteEventHandler: no repository found for project id {id}', ['id' => $event->projectId]);

            return;
        }

        $gitlabUser = $this->api->getUser($event->user->id);
        $user       = $this->userRepository->findOneBy(['email' => $gitlabUser->email]);
        if ($user === null) {
            $this->logger?->info('NoteEventHandler: no user found with email {email}', ['email' => $gitlabUser->email]);

            return;
        }

        /** @var Position $position */
        $position = $event->attributes->position;

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setFilePath($position->oldPath ?? $position->newPath);
        $comment->setCreateTimestamp(time());
        $comment->setUpdateTimestamp(time());
        $comment->setMessage($event->note);

        $test = true;
    }
}
