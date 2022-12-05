<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\DiffTree;

use DR\Review\Entity\Review\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use Psr\Log\LoggerAwareTrait;

class GitDiffTreeService
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $builderFactory,
    ) {
    }

    /**
     * @return string[]
     * @throws RepositoryException
     */
    public function getFilesInRevision(Revision $revision): array
    {
        // clone or pull the repository for the given rule.
        $repository = $this->repositoryService->getRepository((string)$revision->getRepository()?->getUrl());

        $commandBuilder = $this->builderFactory->createDiffTree()
            ->noCommitId()
            ->nameOnly()
            ->recurseSubTree()
            ->hash((string)$revision->getCommitHash());

        $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $revision->getRepository()?->getName()));

        // create `git log ...` command and execute.
        $output = $repository->execute($commandBuilder);

        // cleanup empty lines
        return array_filter(explode("\n", $output), static fn($val) => trim($val) !== '');
    }
}
