<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Checkout;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitCheckoutService
{
    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function checkout(Repository $repository, string $ref): void
    {
        $commandBuilder = $this->commandFactory->createCheckout()->startPoint($ref);

        // create branch
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);
    }

    /**
     * @throws RepositoryException|ProcessFailedException
     */
    public function checkoutRevision(Revision $revision): string
    {
        /** @var Repository $repository */
        $repository = $revision->getRepository();
        $branchName = sprintf('repository-%s-revision-%s', $repository->getId(), $revision->getId());

        $commandBuilder = $this->commandFactory
            ->createCheckout()
            ->branch($branchName)
            ->startPoint($revision->getCommitHash() . '~');

        // checkout revisions
        $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        return $branchName;
    }
}
