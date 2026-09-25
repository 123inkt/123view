<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Api\Gitlab\NoteEvent;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventHandler implements RemoteEventHandlerInterface, LoggerAwareInterface
{
    use ClockAwareTrait;
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repository,
        private readonly MessageBusInterface $bus,
        private readonly GitlabApi $api,
        private readonly UserRepository $userRepository,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly BranchRevisionService $branchRevisionService,
        private readonly RevisionFileRepository $revisionFileRepository,
        private readonly LineReferenceFactory $lineReferenceFactory,
        private readonly CommentRepository $commentRepository,
    ) {
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
            $this->logger?->notice(
                'NoteEventHandler: comment already exists in 123view',
                ['discussionId' => $event->discussionId, 'message' => $event->message]
            );

            return;
        }

        // find gitlab user
        $gitlabUser = $this->api->users()->getUser($event->userId);
        if ($gitlabUser === null) {
            $this->logger?->notice('NoteEventHandler: user {id} not found in gitlab', ['id' => $event->userId, 'discussionId' => $event->discussionId]
            );

            return;
        }

        // find user
        $user = $this->userRepository->findOneBy(['email' => $gitlabUser->email]);
        if ($user === null) {
            $this->logger?->notice(
                'NoteEventHandler: user {email} not found in 123view',
                ['email' => $gitlabUser->email, 'discussionId' => $event->discussionId]
            );

            return;
        }

        // find repository
        $repository = $this->repository->findByProperty('gitlab-project-id', (string)$event->projectId);
        if ($repository === null || $repository->isActive() === false) {
            $this->logger?->notice(
                'NoteEventHandler: repository {id} doesnt exist or is inactive in 123view',
                ['id' => $event->projectId, 'discussionId' => $event->discussionId]
            );

            return;
        }

        // find revisions
        $revisions = $this->branchRevisionService->getRevisionsFor($repository, 'origin/' . $event->sourceBranch, $event->targetBranch);
        if (count($revisions) === 0) {
            $this->logger?->notice(
                'NoteEventHandler: no revisions found for branch {name}',
                ['name' => $event->sourceBranch, 'discussionId' => $event->discussionId]
            );

            return;
        }

        // filter revisions with review
        $revisions = array_filter($revisions, static fn(Revision $revision) => $revision->getReview() !== null);

        // find revision matching filename
        [$revision, $filepath] = $this->matchRevision($event, $revisions);
        if ($revision === null || $filepath === null) {
            $this->logger?->notice(
                'NoteEventHandler: no revision matching file {file}',
                ['file' => $event->newPath ?? $event->oldPath, 'discussionId' => $event->discussionId]
            );

            return;
        }
        $review        = Assert::notNull($revision->getReview());
        $lineReference = new LineReference(
            $event->oldPath,
            $event->newPath,
            $event->oldLine ?? $event->newLine,
            0,
            $event->newLine ?? $event->oldLine,
            $revision->getCommitHash()
        );

        $comment = new Comment();
        $comment->setFilePath($filepath);
        $comment->setTag(null);
        $comment->setLineReference($lineReference);
        $comment->setReview($review);
        $comment->setMessage($event->description);
        $comment->setUser($user);
        $comment->setExtReferenceId(sprintf('%d:%s:%d', $event->mergeRequestIId, $event->discussionId, $event->id));
        $comment->setCreateTimestamp($this->now()->getTimestamp());
        $comment->setUpdateTimestamp($this->now()->getTimestamp());

        $review->getComments()->add($comment);
        $this->commentRepository->save($comment, true);
        $this->logger?->info(
            'NoteEventHandler: creating comment for {file} on {repository}: {review} by {user}',
            [
                'file'         => $event->newPath ?? $event->oldPath,
                'repository'   => $repository->getDisplayName(),
                'review'       => 'CR-' . $review->getProjectId(),
                'user'         => $user->getName(),
                'discussionId' => $event->discussionId
            ]
        );
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array{0: Revision|null, 1: string|null}
     */
    private function matchRevision(NoteEvent $event, array $revisions): array
    {
        foreach (Arrays::removeNull([$event->newPath, $event->oldPath]) as $path) {
            $revision = $this->findRevisionFor($path, $revisions, $event->headSha);
            if ($revision !== null) {
                return [$revision, $path];
            }
        }

        return [null, null];
    }

    private function findRevisionFor(string $filepath, array $revisions, string $preferSha): ?Revision
    {
        $files = $this->revisionFileRepository->findRevisionsForFile($revisions, $filepath);
        foreach ($files as $file) {
            if ($file->getRevision()->getCommitHash() === $preferSha) {
                return $file->getRevision();
            }
        }

        return Arrays::firstOrNull($files)?->getRevision();
    }
}
