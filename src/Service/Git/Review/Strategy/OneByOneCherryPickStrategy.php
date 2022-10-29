<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Service\Git\Add\GitAddService;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;
use DR\GitCommitNotification\Utility\Arrays;

class OneByOneCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
        private readonly GitAddService $addService
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions): array
    {
        // create branch
        $branchName = $this->checkoutService->checkoutRevision(Arrays::first($revisions));

        try {
            foreach ($revisions as $revision) {
                // cherry pick single revision
                $result = $this->cherryPickService->tryCherryPickRevisions([$revision]);

                // cherry pick failed due to merge conflicts. Take theirs:
                if ($result === false) {
                    $this->addService->add($repository, '.');
                }
            }

            $files = $this->diffService->getBundledDiffFromRevisions($repository);
        } finally {
            // reset the repository again
            $this->resetService->resetHard($repository);

            // checkout master
            $this->checkoutService->checkout($repository, 'master');

            // cleanup branch
            $this->branchService->tryDeleteBranch($repository, $branchName);
        }

        return $files;
    }
}
