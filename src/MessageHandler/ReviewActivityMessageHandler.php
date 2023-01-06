<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityProvider;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class ReviewActivityMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CodeReviewActivityProvider $activityProvider,
        private readonly CodeReviewActivityRepository $activityRepository,
        private readonly CodeReviewActivityPublisher $activityPublisher,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(CodeReviewAwareInterface $evt): void
    {
        $activity = $this->activityProvider->fromEvent($evt);
        if ($activity === null) {
            $this->logger?->info('ReviewActivityHandler: no activity for review event: ' . $evt->getName());

            return;
        }

        $this->logger?->info('ReviewActivityHandler: registered activity for review event: ' . $evt->getName());
        $this->activityRepository->save($activity, true);

        $this->logger?->info('ReviewActivityHandler: publish to mercure: ' . $evt->getName());
        $this->activityPublisher->publish($activity);
    }
}
