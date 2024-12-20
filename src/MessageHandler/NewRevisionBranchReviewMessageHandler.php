<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\CodeReviewService;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class NewRevisionBranchReviewMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CodeReviewService $reviewService,
        private readonly CodeReviewerStateResolver $reviewerStateResolver,
        private readonly FileSeenStatusService $seenStatusService,
        private readonly ReviewRevisionEventService $eventService,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(NewRevisionMessage $message): void
    {
        $this->logger?->info("NewRevisionBranchReviewMessageHandler: revision: {id}", ['id' => $message->revisionId]);
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            $this->logger?->info('NewRevisionBranchReviewMessageHandler: no revision found');

            return;
        }

        // find review
        $review = $this->reviewRepository->findOneBy(
            [
                'referenceId' => [$revision->getFirstBranch(), 'origin/' . $revision->getFirstBranch()],
                'type'        => CodeReviewType::BRANCH,
                'repository'  => $revision->getRepository()
            ]
        );
        if ($review === null) {
            $this->logger?->info('NewRevisionBranchReviewMessageHandler: no review found for revision {title}', ['title' => $revision->getTitle()]);

            return;
        }

        $reviewersState = $this->reviewerStateResolver->getReviewersState($review);
        $reviewState    = $review->getState();

        $this->logger?->info(
            'NewRevisionBranchReviewMessageHandler: add revision {hash} to review {title}',
            ['hash' => $revision->getCommitHash(), 'title' => $revision->getTitle()]
        );

        // add revision to review
        $this->reviewService->addRevisions($review, [$revision]);

        // mark files in revision as unseen
        $this->seenStatusService->markAllAsUnseen($review, $revision);

        // dispatch events
        $this->eventService->revisionAddedToReview($review, $revision, false, $reviewState, $reviewersState);
    }
}
