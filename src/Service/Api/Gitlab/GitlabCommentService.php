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

    public function __construct(private readonly PositionFactory $positionFactory, private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function create(GitlabApi $api, Comment $comment, int $mergeRequestIId): void
    {
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
        $referenceId = $api->discussions()->create($projectId, $mergeRequestIId, $position, $comment->getMessage());
        $comment->setExtReferenceId($referenceId);
        $this->commentRepository->save($comment, true);
    }

    /**
     * @throws Throwable
     */
    public function update(GitlabApi $api, Comment $comment): void
    {
        if ($comment->getExtReferenceId() === null) {
            return;
        }

        $projectId = (int)$comment->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        [$mergeRequestIId, $discussionId, $noteId] = explode(':', $comment->getExtReferenceId());

        $this->logger?->info(
            'Updating comment in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        $api->discussions()->update($projectId, (int)$mergeRequestIId, $discussionId, $noteId, $comment->getMessage());
    }

    /**
     * @throws Throwable
     */
    public function resolve(GitlabApi $api, Comment $comment, bool $resolve): void
    {
        if ($comment->getExtReferenceId() === null) {
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
        $api->discussions()->delete($projectId, (int)$mergeRequestIId, $discussionId, $noteId);
    }
}
