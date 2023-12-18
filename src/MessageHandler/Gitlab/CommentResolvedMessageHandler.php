<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class CommentResolvedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabCommentSyncEnabled,
        private readonly CommentRepository $commentRepository,
        private readonly UserRepository $userRepository,
        private readonly GitlabApiProvider $apiProvider,
        private readonly GitlabCommentService $commentService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(CommentResolved|CommentUnresolved $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false) {
            $this->logger?->info('Gitlab comment sync disabled');

            return;
        }

        $comment = Assert::notNull($this->commentRepository->find($event->getCommentId()));
        $user    = Assert::notNull($this->userRepository->find($event->getUserId()));
        $api     = $this->apiProvider->create($comment->getReview()->getRepository(), $user);
        if ($api === null) {
            $this->logger?->info('No api configuration found for comment {comment}', ['comment' => $event->getCommentId()]);

            return;
        }

        $this->commentService->resolve($api, $comment, $event instanceof CommentResolved);
    }
}
