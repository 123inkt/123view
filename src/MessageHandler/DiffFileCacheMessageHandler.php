<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Throwable;

#[AsMessageHandler(fromTransport: 'async_messages')]
class DiffFileCacheMessageHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly ReviewDiffServiceInterface $diffService)
    {
    }

    /**
     * @throws Throwable
     */
    public function handleEvent(WebhookEventInterface $event): void
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        if ($review === null) {
            $this->logger?->info('DiffFileCacheMessageHandler: no review available for id: {review}', ['review' => $event->getReviewId()]);
        }

        $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $review->getRevisions()->toArray());
        $this->logger?->info('DiffFileCacheMessageHandler: diff file cache warmed up for id: {review}', ['review' => $event->getReviewId()]);
    }

    /**
     * @return iterable<string, array<string, string>>
     */
    public static function getHandledMessages(): iterable
    {
        yield ReviewCreated::class => ['method' => 'handleEvent', 'from_transport' => 'async_messages'];
        yield ReviewRevisionAdded::class => ['method' => 'handleEvent', 'from_transport' => 'async_messages'];
        yield ReviewRevisionRemoved::class => ['method' => 'handleEvent', 'from_transport' => 'async_messages'];
    }
}
