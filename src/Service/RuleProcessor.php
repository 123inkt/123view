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
use DR\GitCommitNotification\Service\Mail\MailService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class RuleProcessor
{
    private LoggerInterface          $logger;
    private CommitBundler            $bundler;
    private GitDiffService           $diffService;
    private GitLogService            $gitLogService;
    private CommitFilter             $filter;
    private EventDispatcherInterface $dispatcher;
    private MailService              $mailService;

    public function __construct(
        LoggerInterface $logger,
        GitLogService $gitLogService,
        GitDiffService $diffService,
        CommitFilter $filter,
        CommitBundler $bundler,
        EventDispatcherInterface $dispatcher,
        MailService $mailService
    ) {
        $this->logger        = $logger;
        $this->gitLogService = $gitLogService;
        $this->diffService   = $diffService;
        $this->filter        = $filter;
        $this->bundler       = $bundler;
        $this->dispatcher    = $dispatcher;
        $this->mailService   = $mailService;
    }

    /**
     * @throws Throwable
     */
    public function processRule(RuleConfiguration $ruleConfig): void
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
            $this->logger->info('Found 0 new commits, ending...');

            return;
        }

        // notify event listeners that commits are ready to be mailed
        foreach ($commits as $commit) {
            $this->dispatcher->dispatch(new CommitEvent($commit));
        }

        // send mail
        $this->mailService->sendCommitsMail($ruleConfig, $commits);
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
