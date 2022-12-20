<?php
declare(strict_types=1);

namespace DR\Review\Command\Revision;

use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Service\Git\Log\GitLogService;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('revisions:refresh', 'Refresh currently stored hashes with remote hashes, adding and removing any differences')]
class RefreshRevisionCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private RevisionRepository $revisionRepository,
        private GitLogService $logService,
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);
        $repositories = $this->repositoryRepository->findByValidateRevisions();
        foreach ($repositories as $repository) {
            $this->logger?->info('Checking hashes for {name}', ['name', $repository->getName()]);
            $localHashes  = $this->revisionRepository->getCommitHashes($repository);
            $remoteHashes = $this->logService->getCommitHashes($repository);

            $missing = array_diff($remoteHashes, $localHashes);
            $deleted = array_diff($localHashes, $remoteHashes);

            $this->logger?->info('Found {count} missing hashes for repository {name}', ['count' => count($missing), 'name', $repository->getName()]);
            $this->logger?->info('Found {count} deleted hashes for repository {name}', ['count' => count($missing), 'name', $repository->getName()]);

            foreach ($missing as $hash) {
                $this->bus->dispatch(new CommitAddedMessage($repository->getId(), $hash));
            }
            foreach ($deleted as $hash) {
                $this->bus->dispatch(new CommitRemovedMessage($repository->getId(), $hash));
            }
        }

        return self::SUCCESS;
    }
}
