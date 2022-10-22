<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use Doctrine\ORM\NonUniqueResultException;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Message\AsyncMessageInterface;
use DR\GitCommitNotification\Message\ReviewCreated;
use DR\GitCommitNotification\Message\ReviewOpened;
use DR\GitCommitNotification\Message\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewRevisionMatcher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
class RevisionAddedMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RevisionRepository $revisionRepository,
        private CodeReviewRepository $reviewRepository,
        private CodeReviewRevisionMatcher $reviewRevisionMatcher,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(RevisionAddedMessage $message): void
    {
        $this->logger?->info("MessageHandler: revision: " . $message->revisionId);
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

        $reviewCreated = $review->getId() === null;
        $review->addRevision($revision);

        foreach ($review->getReviewers() as $reviewer) {
            $reviewer->setState(CodeReviewerStateType::OPEN);
        }

        $reviewOpened = false;
        if ($review->getState() === CodeReviewStateType::CLOSED) {
            $review->setState(CodeReviewStateType::OPEN);
            $reviewOpened = true;
        }

        $this->reviewRepository->save($review, true);

        $this->logger?->info('MessageHandler: add revision ' . $revision->getCommitHash() . ' to review ' . $revision->getTitle());

        // dispatch event
        if ($reviewCreated) {
            $this->dispatchAfter(new ReviewCreated((int)$review->getId()));
        }
        if ($reviewOpened) {
            $this->dispatchAfter(new ReviewOpened((int)$review->getId()));
        }
        $this->dispatchAfter(new ReviewRevisionAdded((int)$review->getId(), (int)$revision->getId()));
    }

    private function dispatchAfter(AsyncMessageInterface $event): void
    {
        $this->bus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
    }
}
