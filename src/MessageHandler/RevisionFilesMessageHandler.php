<?php
declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

class RevisionFilesMessageHandler
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionFileRepository $revisionFileRepository,
        private readonly GitDiffService $gitDiffService,
        private readonly GitRepositoryLockManager $lockManager
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'async_messages')]
    public function __invoke(NewRevisionMessage $message): void
    {
        $revision = $this->revisionRepository->find($message->revisionId);
        if ($revision === null) {
            return;
        }

        $files = $this->lockManager->start($revision->getRepository(), fn() => $this->gitDiffService->getRevisionFiles($revision));
        if (count($files) === 0) {
            return;
        }

        foreach ($files as $file) {
            $this->revisionFileRepository->save($file);
            $revision->getFiles()->add($revision);
        }
        $this->revisionRepository->save($revision, true);
    }
}
