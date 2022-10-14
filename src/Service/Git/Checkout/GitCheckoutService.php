<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Checkout;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitCheckoutService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCheckoutCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @throws RepositoryException
     */
    public function checkout(Repository $repository, string $ref): void
    {
        $commandBuilder = $this->commandFactory->create()->branch($ref);

        // create branch
        $output = $this->repositoryService->getRepository($repository->getUrl())->execute($commandBuilder);

        $this->logger->info($output);
    }

    /**
     * @throws RepositoryException
     */
    public function checkoutRevision(Revision $revision): void
    {
        /** @var Repository $repository */
        $repository     = $revision->getRepository();
        $commandBuilder = $this->commandFactory
            ->create()
            ->branch(sprintf('repository-%s-revision-%s', $repository->getId(), $revision->getId()))
            ->startPoint($revision->getCommitHash());

        // create branch
        $output = $this->repositoryService->getRepository($repository->getUrl())->execute($commandBuilder);

        $this->logger->info($output);
    }
}
