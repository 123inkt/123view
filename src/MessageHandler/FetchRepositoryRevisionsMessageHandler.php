<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Review\Revision;
use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Service\Git\Fetch\LockableGitFetchService;
use DR\Review\Service\Git\Log\LockableGitLogService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Review\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class FetchRepositoryRevisionsMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const MAX_COMMITS_PER_MESSAGE = 1000;

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private GitFetchRemoteRevisionService $remoteRevisionService,
        private LockableGitLogService $logService,
        private LockableGitFetchService $fetchService,
        private RevisionRepository $revisionRepository,
        private RevisionFactory $revisionFactory,
        private MessageBusInterface $bus,
        private ?int $maxCommitsPerMessage = self::MAX_COMMITS_PER_MESSAGE
    ) {
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

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(FetchRepositoryRevisionsMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);
        $repository = Assert::notNull($this->repositoryRepository->find($message->repositoryId));

        $commits = $this->remoteRevisionService->fetchRevisionFromRemote($repository, $this->maxCommitsPerMessage);
        $this->logger?->info("MessageHandler: {commits} new commits", ['commits' => count($commits)]);

        // chunk and save it
        /** @var Commit[] $commitChunk */
        foreach (array_chunk($commits, 50) as $commitChunk) {
            $revisions = $this->revisionFactory->createFromCommits($commitChunk);
            $revisions = $this->revisionRepository->saveAll($repository, $revisions);
            $this->dispatchRevisions($revisions);
        }

        if (count($commits) >= $this->maxCommitsPerMessage) {
            $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));
        }
    }
}
