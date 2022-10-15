<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\CherryPick;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Utility\Type;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitCherryPickService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @throws RepositoryException
     */
    public function cherryPickRevisions(array $revisions): void
    {
        /** @var Repository $repository */
        $repository     = Type::notFalse(reset($revisions))->getRepository();
        $commandBuilder = $this->commandFactory
            ->createCheryPick()
            ->strategy('recursive')
            ->conflictResolution('theirs')
            ->noCommit()
            ->hashes(array_map(static fn($revision) => (string)$revision->getCommitHash(), $revisions));

        // merge given hashes
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }

    /**
     * @throws RepositoryException
     */
    public function cherryPickAbort(Repository $repository): void
    {
        $commandBuilder = $this->commandFactory
            ->createCheryPick()
            ->abort();

        // abort cherry-pick
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
