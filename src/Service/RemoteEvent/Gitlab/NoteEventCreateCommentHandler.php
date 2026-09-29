<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Utils\Assert;
use Throwable;

class NoteEventCreateCommentHandler
{
    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly RepositoryRepository $repository,
        private readonly BranchRevisionService $branchRevisionService,
        private readonly RevisionFilepathMatcher $revisionMatcher,
        private readonly CommentFactory $commentFactory,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(NoteEvent $event, User $user): void
    {
        $mergeRequest = Assert::notNull($event->mergeRequest);
        $referenceId  = sprintf('%d:%s:%d', $mergeRequest->mergeRequestIId, $event->discussionId, $event->id);
        if ($this->commentRepository->findOneBy(['extReferenceId' => $referenceId]) !== null) {
            $this->eventLogger->logCommentAlreadyExists($event);

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
}
