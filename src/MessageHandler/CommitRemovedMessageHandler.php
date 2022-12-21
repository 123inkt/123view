<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class CommitRemovedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(CommitRemovedMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);
        $repository = Assert::notNull($this->repositoryRepository->find($message->repositoryId));
        $revision   = $this->revisionRepository->findOneBy(['commitHash' => $message->commitHash, 'repository' => $repository->getId()]);

        if ($revision === null) {
            $this->logger?->info("MessageHandler: no revision for hash {hash}", ['hash' => $message->commitHash]);

            return;
        }

        $review = $revision->getReview();
        if ($review !== null) {
            $review->getRevisions()->removeElement($revision);
            $revision->setReview(null);
            $this->reviewRepository->save($review, true);
            $this->bus->dispatch(new ReviewRevisionRemoved((int)$review->getId(), (int)$revision->getId(), null));
        }
        $this->revisionRepository->remove($revision, true);

        $this->logger?->info("MessageHandler: revision removed {hash}", ['hash' => $message->commitHash]);
    }
}
