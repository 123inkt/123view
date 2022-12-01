<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewActivityRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(fromTransport: 'async_messages')]
class ReviewActivityMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewActivityProvider $activityProvider,
        private readonly CodeReviewActivityRepository $activityRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(WebhookEventInterface $evt): void
    {
        $activity = null;
        if ($evt instanceof ReviewCreated) {
            $activity = $this->activityProvider->fromReviewCreated($evt);
        } elseif ($evt instanceof ReviewAccepted || $evt instanceof ReviewRejected || $evt instanceof ReviewOpened || $evt instanceof ReviewClosed) {
            $activity = $this->activityProvider->fromReviewEvent($evt);
        } elseif ($evt instanceof ReviewRevisionAdded) {
            $activity = $this->activityProvider->fromReviewRevisionAdded($evt);
        } elseif ($evt instanceof CommentAdded) {
            $activity = $this->activityProvider->fromCommendAdded($evt);
        }

        if ($activity === null) {
            $this->logger?->info('ReviewActivityHandler: no activity for review event: ' . $evt->getName());

            return;
        }

        $this->logger?->info('ReviewActivityHandler: registered activity for review event: ' . $evt->getName());
        $this->activityRepository->save($activity, true);
    }
}
