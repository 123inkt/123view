<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Log;

use DateTime;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Parser\GitLogParser;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitLogService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CacheableGitRepositoryService $cachedRepositoryService,
        private readonly GitRepositoryService $gitRepositoryService,
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
                $repository = $this->cachedRepositoryService->getRepository((string)$repositoryConfig->getUrl());

                // create command
                $commandBuilder = $this->commandFactory->fromRule($ruleConfig);

                $this->logger?->info(sprintf('Executing `%s` for `%s`', $commandBuilder, $repositoryConfig->getName()));

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
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsSince(Repository $repository, ?DateTime $since = null, ?int $limit = null): array
    {
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->remotes()
            ->reverse()
            ->dateOrder()
            ->format($this->formatPatternFactory->createPattern());
        if ($since !== null) {
            $command->since($since);
        }

        $this->logger?->info(sprintf('Executing `%s` for `%s`', $command, $repository->getName()));

        // get repository data without cache, fetch new revisions and execute command
        $output = $this->gitRepositoryService->getRepository((string)$repository->getUrl())->execute($command);

        // get commits
        return $this->logParser->parse($repository, $output, $limit);
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function getCommitsFromRange(Repository $repository, string $fromHash, string $toHash): array
    {
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->hashRange($fromHash, $toHash)
            ->format($this->formatPatternFactory->createPattern());

        $this->logger?->info(sprintf('Executing `%s` for `%s`', $command, $repository->getName()));

        // fetch revisions for range
        $output = $this->gitRepositoryService->getRepository((string)$repository->getUrl())->execute($command);

        // get commits
        return $this->logParser->parse($repository, $output);
    }
}
