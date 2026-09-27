<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Utils\Assert;
use Throwable;

readonly class GitlabCommentResolver
{
    public function __construct(private Discussions $discussions, private CommentRepository $commentRepository)
    {
    }

    /**
     * @return array{0: bool, 1: Comment|null}
     * @throws Throwable
     */
    public function resolve(int $projectId, int $mergeRequestIId, string $discussionId, int $noteId): array
    {
        $discussion = $this->discussions->getDiscussion($projectId, $mergeRequestIId, $discussionId);
        $rootNote   = Assert::notNull($discussion['notes'][0] ?? null);
        $isReply    = (int)$rootNote['id'] !== $noteId;

        if ($isReply === false) {
            return [false, null];
        }

        $referenceId = sprintf('%d:%s:%d', $mergeRequestIId, $discussionId, $rootNote['id']);
        $comment     = $this->commentRepository->findOneBy(['extReferenceId' => $referenceId]);

        return [true, $comment];
    }
}
