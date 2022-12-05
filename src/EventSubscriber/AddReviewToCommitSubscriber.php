<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Event\CommitEvent;
use DR\Review\Repository\Review\CodeReviewRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddReviewToCommitSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function onCommitEvent(CommitEvent $event): void
    {
        $commit       = $event->commit;
        $repositoryId = $commit->repository->getId();
        $commitHash   = $commit->commitHashes[0] ?? null;
        if ($repositoryId === null || $commitHash === null || $commit->review !== null) {
            return;
        }

        $commit->review = $this->reviewRepository->findOneByCommitHash($repositoryId, $commitHash);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [CommitEvent::class => ['onCommitEvent']];
    }
}
