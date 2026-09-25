<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\CodeReview\CodeReviewRevisionMatcher;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class NewRevisionMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewService $reviewService,
        private readonly CodeReviewerStateResolver $reviewerStateResolver,
        private readonly CodeReviewRevisionMatcher $reviewRevisionMatcher,
        private readonly FileSeenStatusService $seenStatusService,
        private readonly ManagerRegistry $registry,
        private readonly ReviewRevisionEventService $eventService,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
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

        $reviewCreated  = $review->hasId() === false;
        $reviewersState = $this->reviewerStateResolver->getReviewersState($review);
        $reviewState    = $review->getState();

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
        $this->eventService->revisionAddedToReview($review, $revision, $reviewCreated, $reviewState, $reviewersState);
    }
}
