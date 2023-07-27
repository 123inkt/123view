<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\Strategy;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Add\GitAddService;
use DR\Review\Service\Git\Checkout\GitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Commit\GitCommitService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Utils\Arrays;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Strategy tries to cherry-pick all revision hashes in a single pick, and keeps continuing on conflicts.
 */
class PersistentCherryPickStrategy implements ReviewDiffStrategyInterface
{
    private const MAX_DURATION_IN_SECONDS = 10;

    public function __construct(
        private readonly GitAddService $addService,
        private readonly GitCommitService $commitService,
        private readonly GitCheckoutService $checkoutService,
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
                } catch (RepositoryException|ProcessFailedException $exception) {
                    $this->cherryPickService->cherryPickAbort($repository);

                    throw $exception;
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
        $timeStart = microtime(true);
        for ($attempt = 0; microtime(true) - $timeStart < self::MAX_DURATION_IN_SECONDS; $attempt++) {
            try {
                if ($attempt === 0) {
                    $this->cherryPickService->cherryPickRevisions($revisions, true);
                } else {
                    $this->cherryPickService->cherryPickContinue($repository);
                }
                // successful, uncommit all changes
                $this->resetService->resetSoft($repository, Arrays::first($revisions)->getCommitHash() . '~');

                // get all diff files
                return $this->diffService->getBundledDiffFromRevisions($repository, $options);
            } catch (ProcessFailedException) {
                // add conflicts to the repository
                $this->addService->add($repository, '.');
                // commit changes
                $this->commitService->commit($repository);
                continue;
            }
        }

        throw new RepositoryException('Unable to cherry pick revisions');
    }
}
