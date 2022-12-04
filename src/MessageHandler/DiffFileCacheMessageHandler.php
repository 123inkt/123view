<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
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

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CacheableHighlightedFileService $fileService,
        private readonly FileSeenStatusService $seenStatusService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handleEvent(CodeReviewAwareInterface $event): void
    {
        $review = $this->reviewRepository->find($event->getReviewId());
        if ($review === null) {
            $this->logger?->info('DiffFileCacheMessageHandler: no review available for id: {review}', ['review' => $event->getReviewId()]);

            return;
        }

        $revisions = $review->getRevisions();
        if (count($revisions) === 0) {
            return;
        }

        // fetch diff files for current review and trigger cache refresh
        $files = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions->toArray(), new FileDiffOptions(9999999));
        $this->logger?->info('DiffFileCacheMessageHandler: diff file cache warmed up for id: {review}', ['review' => $event->getReviewId()]);

        // mark all files as unseen
        $this->seenStatusService->markAllAsUnseen($review, $files);

        foreach ($files as $file) {
            if ($file->isDeleted()) {
                continue;
            }

            try {
                $this->fileService->fromDiffFile(Assert::notNull($review->getRepository()), $file);
                $this->logger?->info('DiffFileCacheMessageHandler: file highlight cache warmed up for {file}', ['file' => $file->getPathname()]);
            } catch (Throwable $e) {
                $this->logger?->notice(
                    'DiffFileCacheMessageHandler: failed to highlight: {file}',
                    ['file' => $file->getPathname(), 'exception' => $e]
                );
            }
        }
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
