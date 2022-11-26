<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git;

use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\Branch\GitBranchService;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutService;
use DR\GitCommitNotification\Service\Git\Reset\GitResetService;

class GitRepositoryResetManager
{
    public function __construct(
        private readonly GitCheckoutService $checkoutService,
        private readonly GitResetService $resetService,
        private readonly GitBranchService $branchService,
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
            // reset the repository again
            $this->resetService->resetHard($repository);

            // checkout master
            $this->checkoutService->checkout($repository, 'master');

            // cleanup branch
            $this->branchService->tryDeleteBranch($repository, $branchName);
        }
    }
}
