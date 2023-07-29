<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\CherryPick;

use DR\Review\Entity\Git\CherryPick\CherryPickResult;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitCherryPickService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
        private readonly GitCherryPickParser $cherryPickParser,
    ) {
    }

    /**
     * @param Revision[] $revisions
     */
    public function tryCherryPickRevisions(array $revisions): bool
    {
        try {
            return $this->cherryPickRevisions($revisions)->completed;
        } catch (RepositoryException) {
            return false;
        }
    }

    /**
     * @param Revision[] $revisions
     *
     * @throws RepositoryException
     */
    public function cherryPickRevisions(array $revisions, bool $commit = false): CherryPickResult
    {
        $repository     = Assert::notNull(Arrays::first($revisions)->getRepository());
        $commandBuilder = $this->commandFactory
            ->createCheryPick()
            ->strategy('recursive')
            ->conflictResolution('theirs')
            ->hashes(array_map(static fn($revision) => (string)$revision->getCommitHash(), $revisions));

        if ($commit === false) {
            $commandBuilder->noCommit();
        }

        // merge given hashes
        try {
            $this->repositoryService->getRepository($repository)->execute($commandBuilder);

            return new CherryPickResult(true);
        } catch (ProcessFailedException $exception) {
            $process = $exception->getProcess();

            return $this->cherryPickParser->parse($process->getOutput() . "\n" . $process->getErrorOutput());
        }
    }

    /**
     * @throws RepositoryException
     */
    public function cherryPickContinue(Repository $repository): CherryPickResult
    {
        $commandBuilder = $this->commandFactory
            ->createCheryPick()
            ->continue();

        // continue cherry-pick
        try {
            $this->repositoryService->getRepository($repository)->execute($commandBuilder);

            return new CherryPickResult(true);
        } catch (ProcessFailedException $exception) {
            $process = $exception->getProcess();

            return $this->cherryPickParser->parse($process->getOutput() . "\n" . $process->getErrorOutput());
        }
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
