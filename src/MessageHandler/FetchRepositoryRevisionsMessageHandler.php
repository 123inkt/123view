<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class FetchRepositoryRevisionsMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionFetchService $revisionFetchService,
        private readonly GitRepositoryLockManager $lockManager,
        private readonly GitResetService $resetService
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

        // fetch new revisions
        $this->revisionFetchService->fetchRevisions($repository);

        // reset main branch to the latest revision
        $this->lockManager->start($repository, function () use ($repository) {
            $this->resetService->resetHard($repository, 'origin/' . $repository->getMainBranchName());
        });
    }
}
