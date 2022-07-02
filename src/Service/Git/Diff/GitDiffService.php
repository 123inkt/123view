<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Exception\ParseException;
use DR\GitCommitNotification\Exception\RepositoryException;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use Psr\Log\LoggerInterface;

class GitDiffService
{
    private LoggerInterface               $log;
    private CacheableGitRepositoryService $repositoryService;
    private GitDiffCommandFactory         $commandFactory;
    private DiffParser                    $parser;

    public function __construct(
        LoggerInterface $log,
        CacheableGitRepositoryService $repositoryService,
        GitDiffCommandFactory $commandFactory,
        DiffParser $parser
    ) {
        $this->log               = $log;
        $this->repositoryService = $repositoryService;
        $this->commandFactory    = $commandFactory;
        $this->parser            = $parser;
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

        $this->log->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $commit->repository->getName()));

        // create `git log ...` command and execute.
        $output = $repository->execute($commandBuilder);

        // parse files
        $commit->files = $this->parser->parse($output);

        return $commit;
    }
}
