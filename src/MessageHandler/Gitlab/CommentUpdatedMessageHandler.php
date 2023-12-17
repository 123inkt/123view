<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class CommentUpdatedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabCommentSyncEnabled,
        private readonly CommentRepository $commentRepository,
        private readonly GitlabApiProvider $apiProvider,
        private readonly GitlabCommentService $commentService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(CommentUpdated $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false) {
            return;
        }

        $comment = Assert::notNull($this->commentRepository->find($event->getCommentId()));
        $api     = $this->apiProvider->create($comment->getReview()->getRepository(), $comment->getUser());
        if ($api === null) {
            return;
        }

        $this->commentService->update($api, $comment);
    }
}
