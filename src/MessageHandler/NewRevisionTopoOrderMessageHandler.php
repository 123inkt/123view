<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Revision\RevisionRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class NewRevisionTopoOrderMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(NewRevisionMessage $message): void
    {
        $this->logger?->info("NewRevisionTopoOrderMessageHandler: revision: " . $message->revisionId);
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            return;
        }

        // find revisions in topo order
        if ($revision->getParentHash() !== null) {
            $parents = $this->revisionRepository->findBy(['repository' => $revision->getRepository(), 'commitHash' => $revision->getParentHash()]);
        }

        $children = $this->revisionRepository->findBy(['repository' => $revision->getRepository(), 'parentHash' => $revision->getCommitHash()]);


    }

    private function updateRevisionOrder(Revision $parentRev, Revision $childRev): void
    {
        if ($parentRev->getCreateTimestamp() > $childRev->getCreateTimestamp()) {
            $childTimestamp = $parentRev->getCreateTimestamp() + 1;
            $parentTimestamp = $childRev->getCreateTimestamp() - 1;
        } else {
            $childTimestamp = $childRev->getCreateTimestamp();
            $parentTimestamp = $parentRev->getCreateTimestamp();
        }

    }
}
