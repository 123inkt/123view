<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\GitCommitNotification\Message\Revision\NewRevisionMessage;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Log\LockableGitLogService;
use DR\GitCommitNotification\Service\Revision\RevisionFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler(fromTransport: 'async_revisions')]
class FetchRepositoryRevisionsMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const MAX_COMMITS_PER_MESSAGE = 1000;

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private LockableGitLogService $logService,
        private RevisionRepository $revisionRepository,
        private RevisionFactory $revisionFactory,
        private MessageBusInterface $bus
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
    public function __invoke(FetchRepositoryRevisionsMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);
        $repository = $this->repositoryRepository->find($message->repositoryId);
        if ($repository === null) {
            $this->logger?->critical('MessageHandler: unknown repository: ' . $message->repositoryId);

            return;
        }

        // find the last revision
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);

        // get commits since last visit
        $commits = $this->logService->getCommitsSince($repository, $latestRevision, self::MAX_COMMITS_PER_MESSAGE);
        if (count($commits) === 0) {
            return;
        }

        $this->logger?->info(
            "MessageHandler: found {commits} commits since: {date}",
            ['commits' => count($commits), 'date' => $latestRevision?->getCreateTimestamp()]
        );

        // chunk and save it
        /** @var Commit[] $commitChunk */
        foreach (array_chunk($commits, 50) as $commitChunk) {
            $revisions = $this->revisionFactory->createFromCommits($commitChunk);

            try {
                $this->revisionRepository->saveAll($repository, $revisions);
                $this->dispatchRevisions($revisions);
            } catch (Throwable $exception) {
                $this->logger?->error('review persist failure: {message}', ['message' => $exception->getMessage(), 'exception' => $exception]);
                throw $exception;
            }
        }

        if (count($commits) === self::MAX_COMMITS_PER_MESSAGE) {
            $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));
        }
    }
}
