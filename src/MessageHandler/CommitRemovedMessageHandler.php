<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\Webhook\ReviewRevisionEventService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class CommitRemovedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionVisibilityRepository $visibilityRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly ReviewRevisionEventService $eventService
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
            $reviewState = $review->getState();
            $review->getRevisions()->removeElement($revision);
            $revision->setReview(null);
            if (count($review->getRevisions()) === 0) {
                $review->setState(CodeReviewStateType::CLOSED);
            }

            $this->reviewRepository->save($review, true);
            $this->eventService->revisionRemovedFromReview($review, $revision, $reviewState);
        }

        // remove all visibilities for this revision
        $this->visibilityRepository->removeAll($this->visibilityRepository->findBy(['revision' => $revision->getId()]));

        $this->revisionRepository->remove($revision, true);

        $this->logger?->info("MessageHandler: revision removed {hash}", ['hash' => $message->commitHash]);
    }
}
