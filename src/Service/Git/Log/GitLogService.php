<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Log;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\FormatPattern;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Parser\GitLogParser;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitLogService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $cachedRepositoryService,
        private readonly GitCommandBuilderFactory $commandBuilderFactory,
        private readonly GitRepositoryLockManager $lockManager,
        private readonly GitLogCommandFactory $commandFactory,
        private readonly FormatPatternFactory $formatPatternFactory,
        private readonly GitLogParser $logParser
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
            $output = $this->lockManager->start($repositoryConfig, function () use ($ruleConfig, $repositoryConfig) {
                // clone or pull the repository for the given rule.
                $repository = $this->cachedRepositoryService->getRepository($repositoryConfig);

                // create command
                $commandBuilder = $this->commandFactory->fromRule($ruleConfig);

                // execute `git log ...` command
                return $repository->execute($commandBuilder);
            });

            // parse output
            $commits = $this->logParser->parse($repositoryConfig, $output);

            // to easily parse the remote ref for each commit, the commits are fetched in reverse order. Reverse again to restore order.
            $result[] = array_reverse($commits);
        }

        // merge everything together
        return count($result) === 0 ? [] : array_merge(...$result);
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getCommitHashes(Repository $repository): array
    {
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->remotes()
            ->format(FormatPattern::COMMIT_HASH);

        // get repository and execute command
        $output = $this->cachedRepositoryService->getRepository($repository)->execute($command);

        // cleanup output of any unwanted characters
        $output = (string)preg_replace("/[^\na-zA-Z0-9]+/", '', $output);

        return array_map('trim', explode("\n", trim($output)));
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsFromRange(Repository $repository, string $fromReference, string $toReference): array
    {
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->hashRange($fromReference . '~1', $toReference)
            ->format($this->formatPatternFactory->createPattern());

        // fetch revisions for range
        $output = $this->cachedRepositoryService->getRepository($repository)->execute($command);

        // get commits
        return $this->logParser->parse($repository, $output);
    }
}
