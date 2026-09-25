<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventCreateHandler implements RemoteEventHandlerInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly RepositoryRepository $repository,
        private readonly MessageBusInterface $bus,
        private readonly GitlabApi $api,
        private readonly UserRepository $userRepository,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly BranchRevisionService $branchRevisionService,
        private readonly RevisionFilepathMatcher $revisionMatcher,
        private readonly CommentFactory $commentFactory,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    public function supports(object $event): bool
    {
        return $event instanceof NoteEvent && $event->action === 'create';
    }

    /**
     * @phpstan-param NoteEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);
        $referenceId = sprintf('%d:%s:%d', $event->mergeRequestIId, $event->discussionId, $event->id);
        if ($this->commentRepository->findOneBy(['extReferenceId' => $referenceId])) {
            $this->eventLogger->logCommentAlreadyExists($event);

            return;
        }

        // find gitlab user
        $gitlabUser = $this->api->users()->getUser($event->user->id);
        if ($gitlabUser === null) {
            $this->eventLogger->logGitlabUserNotFound($event);

            return;
        }

        // find user
        $user = $this->userRepository->findOneBy(['email' => $gitlabUser->email]);
        if ($user === null) {
            $this->eventLogger->logUserNotFound($event, $gitlabUser);

            return;
        }

        // find repository
        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null || $repository->isActive() === false) {
            $this->eventLogger->logRepositoryNotFound($event);

            return;
        }

        // find revisions
        $revisions = $this->branchRevisionService->getRevisionsFor($repository, 'origin/' . $event->sourceBranch, $event->targetBranch);
        if (count($revisions) === 0) {
            $this->eventLogger->logRevisionsNotFound($event);

            return;
        }

        // remove all revisions without review
        $revisions = array_filter($revisions, static fn(Revision $revision) => $revision->getReview() !== null);

        // find revision matching filename
        [$revision, $filepath] = $this->revisionMatcher->matchRevision($event, $revisions);
        if ($revision === null || $filepath === null) {
            $this->eventLogger->logRevisionForFilenameNotFound($event);

            return;
        }

        // create comment and save
        $this->commentRepository->save($this->commentFactory->create($event, $user, $revision, $filepath), true);
        $this->eventLogger->logCommentAddedSuccess($event, Assert::notNull($revision->getReview()), $user);
    }
}
