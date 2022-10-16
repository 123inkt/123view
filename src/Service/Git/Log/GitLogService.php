<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitLogService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private CacheableGitRepositoryService $repositoryService,
        private GitCommandBuilderFactory $commandBuilderFactory,
        private GitLogCommandFactory $commandFactory,
        private FormatPatternFactory $formatPatternFactory,
        private GitLogParser $logParser
    ) {
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

            $this->logger?->debug(sprintf('Executing `%s` for `%s`', $commandBuilder, $repositoryConfig->getName()));

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

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsSince(Repository $repository, ?Revision $revision = null, ?int $limit = null): array
    {
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->remotes()
            ->reverse()
            ->format($this->formatPatternFactory->createPattern());
        if ($revision !== null) {
            $command->hashRange($revision->getCommitHash(), 'HEAD');
        }

        // get output
        $output = $this->repositoryService->getRepository((string)$repository->getUrl())->execute($command);

        // get commits
        $commits = $this->logParser->parse($repository, $output);

        // slice it if necessary
        if ($limit !== null) {
            $commits = array_slice($commits, 0, $limit);
        }

        return $commits;
    }
}
