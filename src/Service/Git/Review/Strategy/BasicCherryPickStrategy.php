<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BasicCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions): array
    {
        $revision = reset($revisions);
        assert($revision !== false);

        // create branch
        $branchName = $this->checkoutService->checkoutRevision($revision);

        try {
            // cherry-pick revisions
            $this->cherryPickService->cherryPickRevisions($revisions);

            // get the diff
            $files = $this->diffService->getBundledDiffFromRevisions($repository);
        } catch (RepositoryException|ProcessFailedException $exception) {
            $this->cherryPickService->cherryPickAbort($repository);

            throw $exception;
        } finally {
            // reset the repository again
            $this->resetService->resetHard($repository);

            // checkout master
            $this->checkoutService->checkout($repository, 'master');

            // cleanup branch
            $this->branchService->deleteBranch($repository, $branchName);
        }

        return $files;
    }
}
