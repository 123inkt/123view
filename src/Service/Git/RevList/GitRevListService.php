<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitRevListService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
        private readonly GitRevListParser $parser
    ) {
    }

    /**
     * @return string[]
     * @throws RepositoryException
     */
    public function getCommitsAheadOf(Repository $repository, string $branchName, ?string $targetBranch = null): array
    {
        $targetBranch ??= $repository->getMainBranchName();

        // show all commits ahead of the master branch, excluding merge commits.
        $commandBuilder = $this->commandFactory->createRevList()
            ->commitRange(sprintf('origin/%s', $targetBranch), $branchName)
            ->leftRight()
            ->pretty('oneline')
            ->rightOnly();

        // list commits ahead of master
        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);
        $this->logger?->info($output);

        return $this->parser->parseOneLine($output);
    }
}
