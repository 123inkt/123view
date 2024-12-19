<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\CommitAddedMessage;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Log\LockableGitLogService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class RevisionValidationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly LockableGitLogService $logService,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function validate(Repository $repository): void
    {
        $localHashes  = $this->revisionRepository->getCommitHashes($repository);
        $remoteHashes = $this->logService->getCommitHashes($repository);

        $missing = array_filter(array_diff($remoteHashes, $localHashes), static fn($val) => $val !== '' && $val !== null);
        $deleted = array_filter(array_diff($localHashes, $remoteHashes), static fn($val) => $val !== '' && $val !== null);

        $this->logger?->info('Found {count} missing hashes in repository {name}', ['count' => count($missing), 'name' => $repository->getName()]);
        $this->logger?->info('Found {count} deleted hashes in repository {name}', ['count' => count($deleted), 'name' => $repository->getName()]);

        foreach ($missing as $hash) {
            $this->logger?->info('Adding commit `{hash}` for repository {name}', ['hash' => $hash, 'name' => $repository->getName()]);
            $this->bus->dispatch(new CommitAddedMessage((int)$repository->getId(), $hash));
        }
        foreach ($deleted as $hash) {
            $this->logger?->info('Removing commit `{hash}` from repository {name}', ['hash' => $hash, 'name' => $repository->getName()]);
            $this->bus->dispatch(new CommitRemovedMessage((int)$repository->getId(), $hash));
        }

        // set validate timestamp
        $repository->setValidateRevisionsTimestamp(time());
        $this->repositoryRepository->save($repository, true);
    }
}
