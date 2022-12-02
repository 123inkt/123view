<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\Comment\CommentEventInterface;
use DR\GitCommitNotification\Message\Comment\CommentReplyEventInterface;
use DR\GitCommitNotification\Message\Review\CodeReviewEventInterface;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
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

    private function getActivity(CodeReviewAwareInterface $evt): ?CodeReviewActivity
    {
        if ($evt instanceof ReviewCreated) {
            return $this->activityProvider->fromReviewCreated($evt);
        }
        if ($evt instanceof CodeReviewEventInterface) {
            return $this->activityProvider->fromReviewEvent($evt);
        }
        if ($evt instanceof ReviewRevisionAdded || $evt instanceof ReviewRevisionRemoved) {
            return $this->activityProvider->fromReviewRevisionEvent($evt);
        }
        if ($evt instanceof ReviewerAdded || $evt instanceof ReviewerRemoved) {
            return $this->activityProvider->fromReviewerEvent($evt);
        }
        if ($evt instanceof CommentEventInterface) {
            return $this->activityProvider->fromCommentEvent($evt);
        }
        if ($evt instanceof CommentReplyEventInterface) {
            return $this->activityProvider->fromCommentReplyEvent($evt);
        }

        return null;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(CodeReviewAwareInterface $evt): void
    {
        $activity = $this->getActivity($evt);
        if ($activity === null) {
            $this->logger?->info('ReviewActivityHandler: no activity for review event: ' . $evt->getName());

            return;
        }

        $this->logger?->info('ReviewActivityHandler: registered activity for review event: ' . $evt->getName());
        $this->activityRepository->save($activity, true);
    }
}
