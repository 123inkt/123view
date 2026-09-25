<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Status;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;

class GitStatusService
{
    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $commandFactory,
    ) {
    }

    /**
     * @return string[]
     * @throws RepositoryException
     */
    public function getModifiedFiles(Repository $repository): array
    {
        $commandBuilder = $this->commandFactory->createStatus()->porcelain();

        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        $results = preg_match_all('/^\w{2}\s+(.*?)\s*$/m', $output, $matches);

        return $results === 0 ? [] : $matches[1];
    }
}
