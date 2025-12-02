<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

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
        $this->revisionRepository->findBy(['commitHash' => $revision->]);

    }
}
