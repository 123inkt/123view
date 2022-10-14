<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\CherryPick;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitCherryPickService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCherryPickCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function cherryPickRevisions(array $revisions): void
    {
        /** @var Repository $repository */
        $repository    = reset($revisions)->getRepository();
        $gitRepository = $this->repositoryService->getRepository($repository->getUrl());

        $commandBuilder = $this->commandFactory
            ->create()
            ->strategy('recursive')
            ->conflictResolution('theirs')
            ->noCommit()
            ->hashes(array_map(static fn(Revision $revision) => $revision->getCommitHash(), $revisions));

        // merge given hashes
        $output = $gitRepository->execute($commandBuilder);

        $this->logger->info($output);
    }
}
