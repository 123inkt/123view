<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service;

use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\Service\Filter\CommitFilter;
use DR\GitCommitNotification\Service\Git\Commit\CommitBundler;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffService;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class RuleProcessor
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly GitLogService $gitLogService,
        private readonly GitDiffService $diffService,
        private readonly CommitFilter $filter,
        private readonly CommitBundler $bundler,
        private readonly EventDispatcherInterface $dispatcher,

    ) {
    }

    /**
     * @return Commit[]
     * @throws Throwable
     */
    public function processRule(RuleConfiguration $ruleConfig): array
    {
        $this->logger->info(sprintf('Executing rule `%s`.', $ruleConfig->rule->getName()));

        $commits = $this->gitLogService->getCommits($ruleConfig);

        // bundle similar commits
        $commits = $this->bundler->bundle($commits);

        // Fetch the single diff for commits with multiple commit hashes
        foreach ($commits as $commit) {
            $this->diffService->getBundledDiff($ruleConfig->rule, $commit);
        }

        // include or exclude certain commits
        $commits = $this->filter($ruleConfig->rule, $commits);

        if (count($commits) === 0) {
            return [];
        }

        // notify event listeners that commits are ready to be mailed
        foreach ($commits as $commit) {
            $this->dispatcher->dispatch(new CommitEvent($commit));
        }

        return $commits;
    }

    /**
     * @param Commit[] $commits
     *
     * @return Commit[]
     */
    private function filter(Rule $rule, array $commits): array
    {
        // exclude certain commits
        $exclusions = $rule->getFilters()->filter(static fn(Filter $filter) => $filter->isInclusion() === false);
        if (count($exclusions) > 0) {
            $commits = $this->filter->exclude($commits, $exclusions);
        }

        // include certain commits
        $inclusions = $rule->getFilters()->filter(static fn(Filter $filter) => (bool)$filter->isInclusion());
        if (count($inclusions) > 0) {
            $commits = $this->filter->include($commits, $inclusions);
        }

        return $commits;
    }
}
