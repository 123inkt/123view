<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\Strategy;

use DR\Review\Entity\Repository\Repository;
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

        return $this->resetManager->start($repository, $branchName, function () use ($repository, $revisions, $options) {
            try {
                $failure = false;
                for ($i = 0; $i < 20; $i++) {
                    try {
                        if ($i === 0) {
                            $this->cherryPickService->cherryPickRevisions($revisions, true);
                        } else {
                            $this->cherryPickService->cherryPickContinue($repository);
                        }
                        // finally successful, get all the changes from
                        $this->resetService->resetSoft($repository, Arrays::first($revisions)->getCommitHash() . '~');
                        $failure = false;
                        break;
                    } catch (ProcessFailedException) {
                        // cherry pick conflict
                        // add conflicts to the repository
                        $this->addService->add($repository, '.');
                        // commit changes
                        $this->commitService->commit($repository);
                        $failure = true;
                        continue;
                    }
                }

                if ($failure) {
                    throw new RepositoryException('Unable to cherry pick revisions');
                }

                // get the diff
                return $this->diffService->getBundledDiffFromRevisions($repository, $options);
            } catch (RepositoryException|ProcessFailedException $exception) {
                $this->cherryPickService->cherryPickAbort($repository);

                throw $exception;
            }
        });
    }
}
