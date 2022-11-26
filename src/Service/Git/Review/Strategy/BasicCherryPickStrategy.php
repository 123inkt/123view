<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\GitRepositoryResetManager;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Utility\Arrays;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Strategy tries to cherry-pick all revision hashes in a single pick.
 */
class BasicCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
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
                // cherry-pick revisions
                $this->cherryPickService->cherryPickRevisions($revisions);

                // get the diff
                return $this->diffService->getBundledDiffFromRevisions($repository, $options?->unifiedDiffLines ?? 10);
            } catch (RepositoryException|ProcessFailedException $exception) {
                $this->cherryPickService->cherryPickAbort($repository);

                throw $exception;
            }
        });
    }
}
