<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
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
    public function __invoke(WebhookEventInterface $event): void
    {
        $activity = null;
        if ($event instanceof ReviewCreated) {
            $activity = $this->activityProvider->fromReviewCreated($event);
        } elseif ($event instanceof CommentAdded) {
            $activity = $this->activityProvider->fromCommendAdded($event);
        }

        if ($activity === null) {
            $this->logger?->info('ReviewActivityHandler: no activity for review event: ' . $event->getName());

            return;
        }

        $this->logger?->info('ReviewActivityHandler: registered activity for review event: ' . $event->getName());
        $this->activityRepository->save($activity, true);
    }
}
