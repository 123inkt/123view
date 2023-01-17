<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\CherryPick;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Utility\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     */
    public function tryCherryPickRevisions(array $revisions): bool
    {
        try {
            $this->cherryPickRevisions($revisions);

            return true;
        } catch (RepositoryException|ProcessFailedException) {
            return false;
        }
    }

    /**
     * @param Revision[] $revisions
     *
     * @throws RepositoryException
     */
    public function cherryPickRevisions(array $revisions): void
    {
        /** @var Repository $repository */
        $repository     = Assert::notFalse(reset($revisions))->getRepository();
        $commandBuilder = $this->commandFactory
            ->createCheryPick()
            ->strategy('recursive')
            ->conflictResolution('theirs')
            ->noCommit()
            ->hashes(array_map(static fn($revision) => (string)$revision->getCommitHash(), $revisions));

        // merge given hashes
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

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
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
    }
}
