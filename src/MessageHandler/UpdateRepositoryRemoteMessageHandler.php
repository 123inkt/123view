<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\RepositoryUpdatedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Git\Remote\GitRemoteService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class UpdateRepositoryRemoteMessageHandler
{
    public function __construct(private readonly RepositoryRepository $repositoryRepository, readonly GitRemoteService $remoteService)
    {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(RepositoryUpdatedMessage $evt): void
    {
        $repository = $this->repositoryRepository->find($evt->repositoryId);
        if ($repository === null) {
            return;
        }
        $this->remoteService->updateRemoteUrl($repository);
    }
}
