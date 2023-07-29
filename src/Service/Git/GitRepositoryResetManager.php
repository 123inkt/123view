<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Branch\GitBranchService;
use DR\Review\Service\Git\Checkout\GitCheckoutService;
use DR\Review\Service\Git\CherryPick\GitCherryPickService;
use DR\Review\Service\Git\Clean\GitCleanService;
use DR\Review\Service\Git\Reset\GitResetService;

class GitRepositoryResetManager
{
    public function __construct(
        private readonly GitCherryPickService $cherryPickService,
        private readonly GitCheckoutService $checkoutService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
        private readonly GitCleanService $cleanService
    ) {
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     * @throws RepositoryException
     */
    public function start(Repository $repository, string $branchName, callable $callback): mixed
    {
        try {
            return $callback();
        } finally {
            $this->cherryPickService->tryCherryPickAbort($repository);

            // reset the repository again
            $this->resetService->resetHard($repository);

            // cleanup any local changes
            $this->cleanService->forceClean($repository);

            // checkout master
            $this->checkoutService->checkout($repository, $repository->getMainBranchName());

            // cleanup branch
            $this->branchService->tryDeleteBranch($repository, $branchName);
        }
    }
}
