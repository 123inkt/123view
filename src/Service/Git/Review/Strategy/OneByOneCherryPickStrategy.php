<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Service\Git\Add\GitAddService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\CherryPick\GitCherryPickService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\GitRepositoryResetManager;
use DR\GitCommitNotification\Utility\Arrays;

class OneByOneCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitDiffService $diffService,
        private readonly GitAddService $addService,
        private readonly GitRepositoryResetManager $resetManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDiffFiles(Repository $repository, array $revisions): array
    {
        return $this->resetManager->start(
            $repository,
            $this->checkoutService->checkoutRevision(Arrays::first($revisions)),
            function () use ($repository, $revisions) {
                foreach ($revisions as $revision) {
                    // cherry-pick single revision
                    $result = $this->cherryPickService->tryCherryPickRevisions([$revision]);

                    // cherry-pick failed due to merge conflicts. Take theirs:
                    if ($result === false) {
                        $this->addService->add($repository, '.');
                    }
                }

                return $this->diffService->getBundledDiffFromRevisions($repository);
            }
        );
    }
}
