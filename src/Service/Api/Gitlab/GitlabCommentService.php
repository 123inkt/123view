<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Utils\Arrays;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class GitlabCommentService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly PositionFactory $positionFactory,
        private readonly CommentRepository $commentRepository,
        private readonly GitlabCommentFormatter $commentFormatter,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function create(GitlabApi $api, Comment $comment, int $mergeRequestIId): void
    {
        if ($comment->getExtReferenceId() !== null) {
            $this->logger?->info('Comment already has reference id, unable to create');

            return;
        }

        $projectId = (int)$comment->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        $version   = Arrays::firstOrNull($api->mergeRequests()->versions($projectId, $mergeRequestIId));
        if ($version === null) {
            $this->logger?->info(
                'No merge request version found for review {id} - {ref}',
                ['id' => $comment->getReview()->getId(), 'ref' => $mergeRequestIId]
            );

            return;
        }

        $position = $this->positionFactory->create($version, $comment->getLineReference());

        $this->logger?->info(
            'Adding comment in gitlab: {projectId} {mergeRequestIId} {comment}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'comment' => $comment->getMessage()]
        );

        $message = $this->commentFormatter->format($comment);
        $referenceId = $api->discussions()->createDiscussion($projectId, $mergeRequestIId, $position, $message);
        $comment->setExtReferenceId($referenceId);
        $this->commentRepository->save($comment, true);
    }

    /**
     * @throws Throwable
     */
    public function updateExtReferenceId(GitlabApi $api, Comment $comment, int $mergeRequestIId): void
    {
        $projectId = (int)$comment->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        $reference = $comment->getLineReference();

        foreach ($api->discussions()->getDiscussions($projectId, $mergeRequestIId) as $thread) {
            foreach ($thread['notes'] as $note) {
                // try to match body
                if ($note['body'] !== $comment->getMessage()) {
                    continue;
                }

                // should at least match either new path or old path
                if ($note['position']['old_path'] !== $reference->oldPath && $note['position']['new_path'] !== $reference->newPath) {
                    continue;
                }

                // set reference id and save
                $referenceId = sprintf('%s:%s:%s', $mergeRequestIId, $thread['id'], $note['id']);
                $this->commentRepository->save($comment->setExtReferenceId($referenceId), true);

                return;
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function update(GitlabApi $api, Comment $comment): void
    {
        if ($comment->getExtReferenceId() === null) {
            $this->logger?->info('Comment has no reference id. Unable to update');

            return;
        }

        $projectId = (int)$comment->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        [$mergeRequestIId, $discussionId, $noteId] = explode(':', $comment->getExtReferenceId());

        $this->logger?->info(
            'Updating comment in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );

        $message = $this->commentFormatter->format($comment);
        $api->discussions()->updateNote($projectId, (int)$mergeRequestIId, $discussionId, $noteId, $message);
    }

    /**
     * @throws Throwable
     */
    public function resolve(GitlabApi $api, Comment $comment, bool $resolve): void
    {
        if ($comment->getExtReferenceId() === null) {
            $this->logger?->info('Comment has no reference id. Unable to resolve');

            return;
        }

        $projectId = (int)$comment->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        [$mergeRequestIId, $discussionId,] = explode(':', $comment->getExtReferenceId());

        $this->logger?->info(
            '(Un)resolving comment in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        $api->discussions()->resolve($projectId, (int)$mergeRequestIId, $discussionId, $resolve);
    }

    /**
     * @throws Throwable
     */
    public function delete(GitlabApi $api, Repository $repository, string $extReferenceId): void
    {
        $projectId = (int)$repository->getRepositoryProperty('gitlab-project-id');

        [$mergeRequestIId, $discussionId, $noteId] = explode(':', $extReferenceId);

        $this->logger?->info(
            'Deleting comment in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        try {
            $api->discussions()->deleteNote($projectId, (int)$mergeRequestIId, $discussionId, $noteId);
            // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            $this->logger?->notice('Failed to remove note from Gitlab', ['exception' => $exception]);
        }
        // @codeCoverageIgnoreEnd
    }
}
