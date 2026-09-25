<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\Strategy;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Utils\Arrays;
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
        private readonly RecoverableGitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitRepositoryResetManager $resetManager,
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
                $batches[] = $this->diffService->getDiffFromRevision(Arrays::first($revisions), $options);
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
        $branchName = $this->checkoutService->checkoutRevision(Arrays::first($revisions));

        return $this->resetManager->start($repository, $branchName, function () use ($revisions) {
            $pickable = [];
            foreach ($revisions as $revision) {
                try {
                    $this->cherryPickService->cherryPickRevisions([$revision]);
                    $pickable[] = $revision;
                } catch (RepositoryException|ProcessFailedException) {
                    break;
                }
            }

            return $pickable;
        });
    }
}
