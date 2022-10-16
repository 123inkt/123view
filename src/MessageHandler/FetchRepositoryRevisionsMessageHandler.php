<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\FetchRepositoryRevisionsMessage;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use DR\GitCommitNotification\Service\Revision\RevisionFactory;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FetchRepositoryRevisionsMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const MAX_COMMITS_PER_MESSAGE = 1000;

    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private GitLogService $logService,
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
            $this->bus->dispatch(new RevisionAddedMessage((int)$revision->getId()));
        }
    }

    /**
     * @throws Exception
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

        // build git log command
        $commits = $this->logService->getCommitsSince($repository, $latestRevision, self::MAX_COMMITS_PER_MESSAGE);
        if (count($commits) === 0) {
            return;
        }

        $this->logger?->info(
            "MessageHandler: found {commits} commits since: {date}",
            ['commits' => count($commits), 'date' => $latestRevision?->getCreateTimestamp()]
        );

        // chunk it
        $commitChunks = array_chunk($commits, 50);

        // save
        foreach ($commitChunks as $commitChunk) {
            $revisions = [];

            foreach ($commitChunk as $commit) {
                foreach ($this->revisionFactory->createFromCommit($repository, $commit) as $revision) {
                    // validate revision doesn't already exist in the db, git history doesn't always honor sequential
                    if ($this->revisionRepository->exists($repository, $revision)) {
                        continue;
                    }

                    $this->revisionRepository->save($revision);
                    $revisions[] = $revision;

                    $this->logger?->info("MessageHandler: added revision {hash}", ['hash' => $revision->getCommitHash()]);
                }
            }

            $this->revisionRepository->flush();
            $this->dispatchRevisions($revisions);
        }

        if (count($commits) === self::MAX_COMMITS_PER_MESSAGE) {
            $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));
        }
    }
}
