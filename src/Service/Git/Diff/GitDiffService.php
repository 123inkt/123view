<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Revision;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Parser\DiffParser;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitDiffService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $builderFactory,
        private readonly GitDiffCommandFactory $commandFactory,
        private readonly DiffParser $parser
    ) {
    }

    /**
     * @throws RepositoryException|ParseException
     */
    public function getBundledDiff(Rule $rule, Commit $commit): Commit
    {
        if (count($commit->commitHashes) <= 1) {
            return $commit;
        }

        // clone or pull the repository for the given rule.
        $repository = $this->repositoryService->getRepository((string)$commit->repository->getUrl());

        $commandBuilder = $this->commandFactory->diffHashes($rule, $commit->parentHash, end($commit->commitHashes));

        $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $commit->repository->getName()));

        // create `git log ...` command and execute.
        $output = $repository->execute($commandBuilder);

        // parse files
        $commit->files = $this->parser->parse($output);

        return $commit;
    }

    /**
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getDiffFromRevision(Revision $revision, ?FileDiffOptions $options = null): array
    {
        /** @var Repository $repository */
        $repository     = $revision->getRepository();
        $commandBuilder = $this->builderFactory
            ->createShow()
            ->unified($options?->unifiedDiffLines ?? 10)
            ->startPoint((string)$revision->getCommitHash());

        $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $repository->getName()));

        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        // parse files
        return $this->parser->parse($output);
    }

    /**
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getBundledDiffFromRevisions(Repository $repository, int $unifiedDiffLines = 10): array
    {
        // create git diff HEAD command
        $commandBuilder = $this->builderFactory->createDiff()
            ->hash('HEAD')
            ->diffAlgorithm(DiffAlgorithmType::MYERS)
            ->unified($unifiedDiffLines)
            ->ignoreCrAtEol()
            ->ignoreSpaceAtEol();

        $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $repository->getName()));

        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($commandBuilder);

        // parse files
        return $this->parser->parse($output);
    }
}
