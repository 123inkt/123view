<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Checkout;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $this->logger?->info($output);
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
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        if ($output !== '') {
            $this->logger?->info($output);
        }

        return $branchName;
    }
}
