<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class DiffFileCacheMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CodeReviewRepository $reviewRepository, private readonly ReviewDiffServiceInterface $diffService)
    {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function handleEvent(ReviewCreated|ReviewRevisionAdded|ReviewRevisionRemoved $event): void
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        if ($review === null) {
            $this->logger?->info('DiffFileCacheMessageHandler: no review available for id: {review}', ['review' => $event->getReviewId()]);

            return;
        }

        // @codeCoverageIgnoreStart
        $systemLoad = function_exists('sys_getloadavg') ? (sys_getloadavg()[1] ?? 0) : 0; // system load in the last 5 minutes
        if ($systemLoad >= 1.1) {
            $this->logger?->info(
                'DiffFileCacheMessageHandler: system load too high, skipping cache of review {review}',
                ['review' => $event->getReviewId()]
            );

            return;
        }
        // @codeCoverageIgnoreEnd

        $revisions = $review->getRevisions();
        if (count($revisions) === 0) {
            return;
        }

        // fetch minimum review differences
        $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions->toArray(), new FileDiffOptions(0));

        // fetch diff files for current review and trigger cache refresh
        $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions->toArray(), new FileDiffOptions(9999999));
        $this->logger?->info('DiffFileCacheMessageHandler: diff file cache warmed up for id: {review}', ['review' => $event->getReviewId()]);
    }
}
