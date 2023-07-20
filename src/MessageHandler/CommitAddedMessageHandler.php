<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Service\Revision\RevisionFactory;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class CommitAddedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly LockableGitShowService $showService,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionFactory $revisionFactory,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(CommitAddedMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);
        $repository = Assert::notNull($this->repositoryRepository->find($message->repositoryId));

        $commit = $this->showService->getCommitFromHash($repository, $message->commitHash);
        if ($commit === null) {
            $this->logger?->info("MessageHandler: cant find commit for hash: {hash}", ['hash' => $message->commitHash]);

            return;
        }

        $revisions = $this->revisionFactory->createFromCommit($commit);
        $this->revisionRepository->saveAll($repository, $revisions);

        foreach ($revisions as $revision) {
            $this->bus->dispatch(new NewRevisionMessage((int)$revision->getId()));
        }
    }
}
