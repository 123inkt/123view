<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Utility\Batch;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class FetchRepositoryRevisionsMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private GitFetchRemoteRevisionService $remoteRevisionService,
        private RevisionRepository $revisionRepository,
        private RevisionFactory $revisionFactory,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(FetchRepositoryRevisionsMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);
        $repository = Assert::notNull($this->repositoryRepository->find($message->repositoryId));

        // setup batch to save revisions
        $batch = new Batch(
            500,
            function (array $revisions) use ($repository): void {
                $this->logger?->info("MessageHandler: {revisions} new revisions", ['revisions' => count($revisions)]);
                $revisions = $this->revisionRepository->saveAll($repository, $revisions);
                $this->dispatchRevisions($revisions);
            }
        );

        $commits = $this->remoteRevisionService->fetchRevisionFromRemote($repository);
        foreach ($commits as $commit) {
            $batch->addAll($this->revisionFactory->createFromCommit($commit));
        }
        $batch->flush();
    }

    /**
     * @param Revision[] $revisions
     */
    private function dispatchRevisions(array $revisions): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new NewRevisionMessage((int)$revision->getId()));
        }
    }
}
