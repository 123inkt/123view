<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Revision\NewRevisionMessage;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Git\Review\CodeReviewService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Throwable;

#[AsMessageHandler(fromTransport: 'async_messages')]
class NewRevisionMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RevisionRepository $revisionRepository,
        private CodeReviewService $reviewService,
        private CodeReviewRevisionMatcher $reviewRevisionMatcher,
        private FileSeenStatusService $seenStatusService,
        private ManagerRegistry $registry,
        private MessageBusInterface $bus
    ) {
    }

    private function dispatchAfter(AsyncMessageInterface $event): void
    {
        $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
    }

    /**
     * @throws Throwable
     */
    public function __invoke(NewRevisionMessage $message): void
    {
        $this->logger?->info("MessageHandler: revision: " . $message->revisionId);
        $revision = $this->revisionRepository->find($message->revisionId);

        // check if we should match this revision
        if ($this->reviewRevisionMatcher->isSupported($revision) === false || $revision === null) {
            return;
        }

        // find or create review and add revision
        $review = $this->reviewRevisionMatcher->match($revision);
        if ($review === null) {
            $this->logger?->info('MessageHandler: no code review for commit message ' . $revision->getTitle());

            return;
        }

        $reviewCreated = $review->getId() === null;
        $reviewState   = $review->getState();

        try {
            $this->reviewService->addRevisions($review, [$revision]);
        } catch (Throwable $exception) {
            $this->logger?->error('review persist failure: {message}', ['message' => $exception->getMessage(), 'exception' => $exception]);
            $this->registry->resetManager();
            throw $exception;
        }

        $this->logger?->info('MessageHandler: add revision ' . $revision->getCommitHash() . ' to review ' . $revision->getTitle());

        // mark files in revision as unseen
        $this->seenStatusService->markAllAsUnseen($review, $revision);

        // dispatch events
        if ($reviewCreated) {
            $this->dispatchAfter(new ReviewCreated((int)$review->getId(), (int)$revision->getId()));
        }
        if ($reviewState === CodeReviewStateType::CLOSED && $review->getState() === CodeReviewStateType::OPEN) {
            $this->dispatchAfter(new ReviewOpened((int)$review->getId(), null));
        }
        $this->dispatchAfter(new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId(), null));
    }
}
