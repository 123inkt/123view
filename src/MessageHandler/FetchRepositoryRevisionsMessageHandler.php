<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class FetchRepositoryRevisionsMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private RepositoryRepository $repositoryRepository, private RevisionFetchService $revisionFetchService)
    {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(FetchRepositoryRevisionsMessage $message): void
    {
        $this->logger?->info("MessageHandler: repository: " . $message->repositoryId);

        $this->revisionFetchService->fetchRevisions(Assert::notNull($this->repositoryRepository->find($message->repositoryId)));
    }
}
