<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentReplyService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class CommentReplyAddedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabCommentSyncEnabled,
        private readonly CommentReplyRepository $replyRepository,
        private readonly GitlabApiProvider $apiProvider,
        private readonly GitlabCommentReplyService $commentReplyService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(CommentReplyAdded|CommentReplyUpdated $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false) {
            $this->logger?->info('Gitlab comment sync disabled. Comment id: {id}', ['id' => $event->commentReplyId]);
            return;
        }

        $reply   = Assert::notNull($this->replyRepository->find($event->getCommentReplyId()));
        $comment = $reply->getComment();
        $api     = $this->apiProvider->create($comment->getReview()->getRepository(), $reply->getUser());
        if ($api === null) {
            $this->logger?->info('No api configuration found for comment reply {id}', ['id' => $event->getCommentReplyId()]);

            return;
        }

        if ($event instanceof CommentReplyAdded) {
            $this->commentReplyService->create($api, $reply);
        } else {
            $this->commentReplyService->update($api, $reply);
        }
    }
}
