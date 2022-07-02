<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use Exception;
use Psr\Log\LoggerInterface;

class GitLogService
{
    private CacheableGitRepositoryService $repositoryService;
    private GitLogCommandFactory          $commandFactory;
    private GitLogParser                  $logParser;
    private LoggerInterface               $log;

    public function __construct(
        LoggerInterface $log,
        CacheableGitRepositoryService $repositoryService,
        GitLogCommandFactory $commandFactory,
        GitLogParser $logParser
    ) {
        $this->log               = $log;
        $this->repositoryService = $repositoryService;
        $this->commandFactory    = $commandFactory;
        $this->logParser         = $logParser;
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommits(RuleConfiguration $ruleConfig): array
    {
        $rule   = $ruleConfig->rule;
        $result = [];

        foreach ($rule->getRepositories() as $repositoryConfig) {
            // clone or pull the repository for the given rule.
            $repository = $this->repositoryService->getRepository((string)$repositoryConfig->getUrl());

            // create command
            $commandBuilder = $this->commandFactory->fromRule($ruleConfig);

            $this->log->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $repositoryConfig->getName()));

            // execute `git log ...` command
            $output = $repository->execute($commandBuilder);

            // parse output
            $commits = $this->logParser->parse($repositoryConfig, $output);

            // to easily parse the remote ref for each commit, the commits are fetched in reverse order. Reverse again to restore order.
            $result[] = array_reverse($commits);
        }

        // merge everything together
        return count($result) === 0 ? [] : array_merge(...$result);
    }
}
