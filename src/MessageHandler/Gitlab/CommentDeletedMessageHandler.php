<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class CommentDeletedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabCommentSyncEnabled,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly UserRepository $userRepository,
        private readonly GitlabApiProvider $apiProvider,
        private readonly GitlabCommentService $commentService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'sync')]
    public function __invoke(CommentRemoved $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false || $event->extReferenceId === null) {
            return;
        }

        $repository = Assert::notNull($this->reviewRepository->find($event->getReviewId()))->getRepository();
        $user       = Assert::notNull($this->userRepository->find($event->getUserId()));
        $api        = $this->apiProvider->create($repository, $user);
        if ($api === null) {
            return;
        }

        $this->commentService->delete($api, $repository, $event->extReferenceId);
    }
}
