<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class GitlabCommentReplyService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CommentReplyRepository $replyRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function create(GitlabApi $api, CommentReply $reply): void
    {
        $commentReferenceId = $reply->getComment()->getExtReferenceId();
        if ($commentReferenceId === null || $reply->getExtReferenceId() !== null) {
            $this->logger?->info('Comment reference id is null, or reply already has an external reference id');

            return;
        }

        $projectId = (int)$reply->getComment()->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        [$mergeRequestIId, $discussionId] = explode(':', $commentReferenceId);

        $this->logger?->info(
            'Adding note on comment gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        $noteId = $api->discussions()->createNote($projectId, (int)$mergeRequestIId, $discussionId, $reply->getMessage());

        $reply->setExtReferenceId($noteId);
        $this->replyRepository->save($reply, true);
    }

    /**
     * @throws Throwable
     */
    public function update(GitlabApi $api, CommentReply $reply): void
    {
        if ($reply->getExtReferenceId() === null) {
            $this->logger?->info('Comment reply has no reference id');

            return;
        }

        $projectId = (int)$reply->getComment()->getReview()->getRepository()->getRepositoryProperty('gitlab-project-id');
        [$mergeRequestIId, $discussionId, $noteId] = explode(':', $reply->getExtReferenceId());

        $this->logger?->info(
            'Updating comment note in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        $api->discussions()->updateNote($projectId, (int)$mergeRequestIId, $discussionId, $noteId, $reply->getMessage());
    }
}
