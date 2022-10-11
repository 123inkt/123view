<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher;
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
        private RevisionPatternMatcher $patternMatcher,
        private ManagerRegistry $registry

    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(RevisionAddedMessage $message): void
    {
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            $this->logger?->notice('MessageHandler: unknown revision: ' . $message->revisionId);
            return;
        }

        // normalize message
        $commitMessage = preg_replace('/^Revert\s+"(.*)"$/', '$1', trim($revision->getTitle()));

        $match = $this->patternMatcher->match($commitMessage);
        if ($match === null) {
            $this->logger?->info('MessageHandler: revision doesn\'t match pattern: ' . $revision->getTitle());
            return;
        }

        /** @var CodeReview|null $review */
        $review = $this->registry
            ->getManager()
            ->getRepository(CodeReview::class)->createQueryBuilder('c')
            ->where('o.title LIKE :match')
            ->setParameter('match', $match . '%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        // create new review
        if ($review === null) {
            $review = new CodeReview();
            $review->setTitle($commitMessage);
            $review->setRepository($revision->getRepository());
            $this->logger?->info('MessageHandler: new code review created for: ' . $commitMessage);
        }

        // add revision to review
        $revision->setReview($review);
        $review->getRevisions()->add($revision);

        // save it
        $this->registry->getManager()->persist($revision);
        $this->registry->getManager()->persist($review);
        $this->registry->getManager()->flush();

        $this->logger?->info('MessageHandler: add revision ' . $revision->getCommitHash() . ' to review ' . $commitMessage);
    }
}
