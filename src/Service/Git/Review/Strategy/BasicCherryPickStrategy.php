<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review\Strategy;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryResetManager;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Utils\Arrays;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Strategy tries to cherry-pick all revision hashes in a single pick.
 */
class BasicCherryPickStrategy implements ReviewDiffStrategyInterface
{
    public function __construct(
        private readonly RecoverableGitCheckoutService $checkoutService,
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
                if ($this->cherryPickService->cherryPickRevisions($revisions)->completed === false) {
                    throw new RepositoryException('Cherry-pick failed');
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
