<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Utility\Arrays;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * 1) Try to cherry-pick 1 revision at a time until it fails.
 * 2) Grab the diffs for the ones succeed
 * 3) Reset branch
 * 4) While more revisions return to step 1
 * 5) Strategy succeeds if the diff bundles can be combined (eg no filepath overlaps)
 */
class HesitantCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
        private readonly BasicCherryPickStrategy $basicCherryPickStrategy,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions, ?FileDiffOptions $options = null): array
    {
        $max     = count($revisions) + 50;
        $batches = [];
        for ($i = 0; $i < $max; $i++) {
            if (count($revisions) === 1) {
                $batches[] = $this->diffService->getDiffFromRevision(Arrays::first($revisions));
                break;
            }

            $pickableRevisions = $this->tryCherryPick($repository, $revisions);
            $batches[]         = $this->basicCherryPickStrategy->getDiffFiles($repository, $pickableRevisions, $options);
            $revisions         = array_slice($revisions, count($pickableRevisions));

            if (count($revisions) === 0) {
                break;
            }
        }

        return array_merge(...$batches);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     * @throws RepositoryException
     */
    private function tryCherryPick(Repository $repository, array $revisions): array
    {
        $pickable   = [];
        $branchName = $this->checkoutService->checkoutRevision(Arrays::first($revisions));

        foreach ($revisions as $revision) {
            try {
                $this->cherryPickService->cherryPickRevisions([$revision]);
                $pickable[] = $revision;
            } catch (RepositoryException|ProcessFailedException) {
                break;
            }
        }

        $this->resetService->resetHard($repository);
        $this->checkoutService->checkout($repository, 'master');
        $this->branchService->tryDeleteBranch($repository, $branchName);

        return $pickable;
    }
}
