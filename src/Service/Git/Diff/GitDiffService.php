<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use DR\Review\Exception\ParseException;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Parser\DiffNumStatParser;
use DR\Review\Service\Parser\PrunableDiffParser;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitDiffService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $repositoryService,
        private readonly GitCommandBuilderFactory $builderFactory,
        private readonly GitDiffCommandFactory $commandFactory,
        private readonly PrunableDiffParser $parser,
        private readonly DiffNumStatParser $numStatParser
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
        $repository = $this->repositoryService->getRepository($commit->repository);

        $commandBuilder = $this->commandFactory->diffHashes($rule, $commit->parentHash, end($commit->commitHashes));

        // create `git log ...` command and execute.
        $output = $repository->execute($commandBuilder);

        // parse files
        $commit->files = $this->parser->parse($output, null);

        return $commit;
    }

    /**
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getDiffFromRevision(Revision $revision, ?FileDiffOptions $options = null): array
    {
        $repository     = $revision->getRepository();
        $commandBuilder = $this->builderFactory
            ->createShow()
            ->ignoreCrAtEol()
            ->ignoreSpaceAtEol()
            ->unified($options?->unifiedDiffLines ?? 10)
            ->startPoint($revision->getCommitHash());

        if ($options?->comparePolicy === DiffComparePolicy::TRIM) {
            $commandBuilder->ignoreSpaceChange();
        } elseif (in_array($options?->comparePolicy, [DiffComparePolicy::IGNORE, DiffComparePolicy::IGNORE_EMPTY_LINES], true)) {
            $commandBuilder->ignoreAllSpace();
        }

        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        // parse files
        return $this->parser->parse($output, $options?->comparePolicy, $options->includeRaw ?? false);
    }

    /**
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getBundledDiffFromRevisions(Repository $repository, ?FileDiffOptions $options = null): array
    {
        // create git diff HEAD command
        $commandBuilder = $this->builderFactory->createDiff()
            ->hash('HEAD')
            ->diffAlgorithm(DiffAlgorithmType::MYERS)
            ->unified($options?->unifiedDiffLines ?? 10)
            ->ignoreCrAtEol()
            ->ignoreSpaceAtEol();

        if ($options?->comparePolicy === DiffComparePolicy::TRIM) {
            $commandBuilder->ignoreSpaceChange();
        } elseif (in_array($options?->comparePolicy, [DiffComparePolicy::IGNORE, DiffComparePolicy::IGNORE_EMPTY_LINES], true)) {
            $commandBuilder->ignoreAllSpace();
        }

        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        // parse files
        return $this->parser->parse($output, $options?->comparePolicy, $options->includeRaw ?? false);
    }

    /**
     * @return DiffFile[]
     * @throws RepositoryException|ParseException
     */
    public function getBundledDiffFromBranch(
        Repository $repository,
        string $sourceBranch,
        string $targetBranch,
        ?FileDiffOptions $options = null
    ): array {
        // create git diff HEAD command
        $commandBuilder = $this->builderFactory->createDiff()
            ->hash($targetBranch . '...' . $sourceBranch)
            ->diffAlgorithm(DiffAlgorithmType::MYERS)
            ->unified($options?->unifiedDiffLines ?? 10)
            ->ignoreCrAtEol()
            ->ignoreSpaceAtEol();

        if ($options?->comparePolicy === DiffComparePolicy::TRIM) {
            $commandBuilder->ignoreSpaceChange();
        } elseif (in_array($options?->comparePolicy, [DiffComparePolicy::IGNORE, DiffComparePolicy::IGNORE_EMPTY_LINES], true)) {
            $commandBuilder->ignoreAllSpace();
        }

        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        // parse files
        return $this->parser->parse($output, $options?->comparePolicy, $options->includeRaw ?? false);
    }

    /**
     * @return RevisionFile[]
     * @throws RepositoryException
     */
    public function getRevisionFiles(Revision $revision): array
    {
        $commandBuilder = $this->builderFactory->createDiff()
            ->hash($revision->getCommitHash() . '^!')
            ->numStat();

        $output = $this->repositoryService->getRepository($revision->getRepository())->execute($commandBuilder);

        return $this->numStatParser->parse($revision, $output);
    }
}
