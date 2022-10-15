<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Checkout;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitCheckoutService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);
    }

    /**
     * @throws RepositoryException
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
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        $this->logger?->info($output);

        return $branchName;
    }
}
