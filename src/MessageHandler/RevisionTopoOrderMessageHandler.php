<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Message\Revision\SortRevisionMessage;
use DR\Review\Repository\Revision\RevisionRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\UuidV7;
use Throwable;

class RevisionTopoOrderMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly RevisionRepository $revisionRepository, private readonly EntityManagerInterface $doctrine)
    {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(NewRevisionMessage|SortRevisionMessage $message): void
    {
        $this->logger?->info("RevisionTopoOrderMessageHandler: revision: " . $message->revisionId);
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            return;
        }

        // set revision uuid
        $revision->setSort(UuidV7::generate((new DateTimeImmutable())->setTimestamp($revision->getCreateTimestamp())));
        $this->revisionRepository->save($revision);

        // find parent revisions and update topological order
        if ($revision->getParentHash() !== null) {
            $parentRevs = $this->revisionRepository->findBy(['repository' => $revision->getRepository(), 'commitHash' => $revision->getParentHash()]);
            foreach ($parentRevs as $parentRev) {
                $this->updateRevisionOrder($parentRev, $revision);
            }
        }

        // find child revisions and update topological order
        $childRevs = $this->revisionRepository->findBy(['repository' => $revision->getRepository(), 'parentHash' => $revision->getCommitHash()]);
        foreach ($childRevs as $childRev) {
            $this->updateRevisionOrder($revision, $childRev);
        }
        $this->doctrine->flush();
        $this->doctrine->clear();
    }

    private function updateRevisionOrder(Revision $parentRev, Revision $childRev): void
    {
        if ($parentRev->getCreateTimestamp() > $childRev->getCreateTimestamp()) {
            $childTimestamp = $parentRev->getCreateTimestamp() + 1;
            $childRev->setSort(null);
        } else {
            $childTimestamp = $childRev->getCreateTimestamp();
        }

        if ($parentRev->getSort() === null) {
            $parentRev->setSort(UuidV7::generate((new DateTimeImmutable())->setTimestamp($parentRev->getCreateTimestamp())));
            $this->revisionRepository->save($parentRev);
        }
        if ($childRev->getSort() !== null) {
            return;
        }

        $childRev->setSort(UuidV7::generate((new DateTimeImmutable())->setTimestamp($childTimestamp)));
        $this->revisionRepository->save($childRev);
    }
}
