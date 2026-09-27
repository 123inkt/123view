<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentReplyFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Review\Service\User\GitlabUserService;
use DR\Utils\Assert;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventCreateHandler implements RemoteEventHandlerInterface
{
    /**
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly RepositoryRepository $repository,
        private readonly GitlabUserService $userService,
        private readonly BranchRevisionService $branchRevisionService,
        private readonly RevisionFilepathMatcher $revisionMatcher,
        private readonly CommentFactory $commentFactory,
        private readonly CommentReplyFactory $commentReplyFactory,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $commentReplyRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @phpstan-impure
     */
    public function supports(object $event): bool
    {
        return $event instanceof NoteEvent && $event->action === 'create' && $event->noteType === 'MergeRequest';
    }

    /**
     * @phpstan-param NoteEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);
        $mergeRequest = Assert::notNull($event->mergeRequest);
        $referencePrefix = sprintf('%d:%s:', $mergeRequest->mergeRequestIId, $event->discussionId);
        $referenceId     = $referencePrefix . $event->id;
        if ($this->commentRepository->findOneBy(['extReferenceId' => $referenceId]) !== null) {
            $this->eventLogger->logCommentAlreadyExists($event);

            return;
        }

        $parentComment = $this->commentRepository->findOneByExtReferenceIdPrefix($referencePrefix);
        if ($parentComment !== null) {
            $this->createReply($event, $parentComment, $referenceId);

            return;
        }

        // find user
        $user = $this->userService->getUser($event->user->id, $event->user->name);
        if ($user === null) {
            $this->eventLogger->logUserNotFound($event, $event->user);

            return;
        }

        // find repository
        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null || $repository->isActive() === false) {
            $this->eventLogger->logRepositoryNotFound($event);

            return;
        }

        // find revisions
        $revisions = $this->branchRevisionService->getRevisionsFor($repository, 'origin/' . $mergeRequest->sourceBranch, $mergeRequest->targetBranch);
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

    /**
     * @throws Throwable
     */
    private function createReply(NoteEvent $event, Comment $comment, string $referenceId): void
    {
        if ($this->commentReplyRepository->findOneBy(['extReferenceId' => $referenceId]) !== null) {
            $this->eventLogger->logCommentAlreadyExists($event, true);

            return;
        }

        $user = $this->userService->getUser($event->user->id, $event->user->name);
        if ($user === null) {
            $this->eventLogger->logUserNotFound($event, $event->user);

            return;
        }

        $reply = $this->commentReplyFactory->create($event, $user, $comment);
        $this->commentReplyRepository->save($reply, true);
        $this->bus->dispatch(new CommentReplyAdded(
            $comment->getReview()->getId(),
            $reply->getId(),
            $user->getId(),
            $reply->getMessage(),
            $comment->getFilePath(),
        ));
        $this->eventLogger->logCommentReplyAddedSuccess($event, $comment->getReview(), $user);
    }
}
