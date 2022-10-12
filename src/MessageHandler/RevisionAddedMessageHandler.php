<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewFactory;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

#[AsMessageHandler]
class RevisionAddedMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RevisionRepository $revisionRepository,
        private CodeReviewRepository $reviewRepository,
        private CodeReviewRevisionMatcher $reviewRevisionMatcher,
        private CodeReviewFactory $reviewFactory
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(RevisionAddedMessage $message): void
    {
        $this->logger->info("MessageHandler: revision: " . $message->revisionId);
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            $this->logger?->notice('MessageHandler: unknown revision: ' . $message->revisionId);

            return;
        }

        // find or create review and add revision
        $review = $this->reviewRevisionMatcher->match($revision);
        if ($review === null) {
            $this->logger?->info('MessageHandler: no code review for commit message ' . $revision->getTitle());

            return;
        }

        $review->addRevision($revision);
        $this->reviewRepository->save($review, true);

        $this->logger?->info('MessageHandler: add revision ' . $revision->getCommitHash() . ' to review ' . $revision->getTitle());
    }
}
