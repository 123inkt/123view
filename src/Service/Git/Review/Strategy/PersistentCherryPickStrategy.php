<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\Strategy;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Add\GitAddService;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Commit\GitCommitService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Status\GitStatusService;
use DR\Utils\Arrays;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Strategy tries to cherry-pick all revision hashes in a single pick, and keeps continuing on conflicts.
 */
class PersistentCherryPickStrategy implements ReviewDiffStrategyInterface
{
    private const MAX_ATTEMPTS = 30;

    public function __construct(
        private readonly GitAddService $addService,
        private readonly GitStatusService $statusService,
        private readonly GitCommitService $commitService,
        private readonly RecoverableGitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitResetService $resetService,
        private readonly GitDiffService $diffService,
        private readonly GitRepositoryResetManager $resetManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        // create branch
        $branchName = $this->checkoutService->checkoutRevision(Arrays::first($revisions));

        return $this->resetManager->start(
            $repository,
            $branchName,
            function () use ($repository, $revisions, $options): array {
                try {
                    return $this->getDiff($repository, $revisions, $options);
                    // @codeCoverageIgnoreStart
                } catch (RepositoryException|ProcessFailedException $exception) {
                    $this->cherryPickService->cherryPickAbort($repository);

                    throw $exception;
                    // @codeCoverageIgnoreEnd
                }
            }
        );
    }

    /**
     * @param Revision[] $revisions
     *
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    private function getDiff(Repository $repository, array $revisions, ?FileDiffOptions $options): array
    {
        $conflicts = [];

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            if ($i === 0) {
                $result = $this->cherryPickService->cherryPickRevisions($revisions, true);
            } else {
                $result = $this->cherryPickService->cherryPickContinue($repository);
            }

            if ($result->completed) {
                // successful, unstash all changes
                $this->resetService->resetSoft($repository, Arrays::first($revisions)->getCommitHash() . '~');

                // get all diff files
                $files = $this->diffService->getBundledDiffFromRevisions($repository, $options);

                // mark which files have merge conflicts
                return $this->markMergeConflicts($files, array_merge(...$conflicts));
            }

            // keep track of conflict files
            $conflicts[] = $result->conflicts;

            // add conflicts to the repository
            $modifiedFiles = $this->statusService->getModifiedFiles($repository);
            $this->addService->add($repository, implode(' ', $modifiedFiles));

            // commit changes
            $this->commitService->commit($repository);
        }

        // @codeCoverageIgnoreStart
        throw new RepositoryException('Unable to cherry pick revisions');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param DiffFile[] $files
     * @param string[]   $conflicts
     *
     * @return DiffFile[]
     */
    private function markMergeConflicts(array $files, array $conflicts): array
    {
        // create lookup table for files in conflict
        $conflictLookup = array_flip($conflicts);

        // mark files in conflict as conflicted
        foreach ($files as $file) {
            $filePath = $file->filePathAfter ?? $file->filePathBefore;
            if ($filePath !== null) {
                $file->hasMergeConflict = isset($conflictLookup[$filePath]);
            }
        }

        return $files;
    }
}
