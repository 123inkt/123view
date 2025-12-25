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
        $repository     = Arrays::first($revisions)->getRepository();
        $commandBuilder = $this->commandFactory
            ->createCherryPick()
            ->strategy('ort')
            ->conflictResolution('theirs')
            ->hashes(array_map(static fn($revision) => $revision->getCommitHash(), $revisions));

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
            ->createCherryPick()
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
    public function tryCherryPickAbort(Repository $repository): bool
    {
        $commandBuilder = $this->commandFactory
            ->createCherryPick()
            ->abort();

        // abort cherry-pick
        try {
            $this->repositoryService->getRepository($repository)->execute($commandBuilder);

            return true;
        } catch (ProcessFailedException) {
            return false;
        }
    }

    /**
     * @throws RepositoryException
     */
    public function cherryPickAbort(Repository $repository): void
    {
        $commandBuilder = $this->commandFactory
            ->createCherryPick()
            ->abort();

        // abort cherry-pick
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }
}
