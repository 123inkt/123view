<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Service\Util\SystemLoadService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class DiffFileCacheMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly SystemLoadService $systemLoadService
    ) {
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

        if ($review->getType() === CodeReviewType::BRANCH) {
            return;
        }

        if ($this->systemLoadService->getLoad() >= 1.1) {
            $this->logger?->info(
                'DiffFileCacheMessageHandler: system load too high, skipping cache of review {review}',
                ['review' => $review->getId()]
            );

            return;
        }

        $revisions = $review->getRevisions();
        if (count($revisions) === 0) {
            return;
        }

        // fetch diff files for current review and trigger cache refresh
        $this->diffService->getDiffForRevisions(
            Assert::notNull($review->getRepository()),
            $revisions->toArray(),
            new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, DiffComparePolicy::ALL)
        );

        $this->logger?->info('DiffFileCacheMessageHandler: diff file cache warmed up for id: {review}', ['review' => $review->getId()]);
    }
}
