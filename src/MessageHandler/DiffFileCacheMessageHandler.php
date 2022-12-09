<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeHighlight\CacheableHighlightedFileService;
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

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly CacheableHighlightedFileService $fileService
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

        $revisions = $review->getRevisions();
        if (count($revisions) === 0) {
            return;
        }

        // fetch diff files for current review and trigger cache refresh
        $files = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions->toArray(), new FileDiffOptions(9999999));
        $this->logger?->info('DiffFileCacheMessageHandler: diff file cache warmed up for id: {review}', ['review' => $event->getReviewId()]);

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
}
