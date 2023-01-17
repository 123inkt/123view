<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\DiffTree;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Utility\Assert;
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
        $repository = $this->repositoryService->getRepository(Assert::notNull($revision->getRepository()));

        $commandBuilder = $this->builderFactory->createDiffTree()
            ->noCommitId()
            ->nameOnly()
            ->recurseSubTree()
            ->hash((string)$revision->getCommitHash());

        // create `git log ...` command and execute.
        $output = $repository->execute($commandBuilder);

        // cleanup empty lines
        return array_filter(explode("\n", $output), static fn($val) => trim($val) !== '');
    }
}
