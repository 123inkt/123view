<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitLogCommandFactory
{
    private GitLogCommandBuilder $builder;
    private FormatPatternFactory $patternFactory;

    public function __construct(GitLogCommandBuilder $builder, FormatPatternFactory $patternFactory)
    {
        $this->builder        = $builder;
        $this->patternFactory = $patternFactory;
    }

    public function fromRule(RuleConfiguration $ruleConfig): GitCommandBuilderInterface
    {
        $rule    = $ruleConfig->rule;
        $options = $rule->getRuleOptions();
        $this->builder
            ->start()
            ->remotes()
            ->topoOrder()
            ->patch()
            ->decorate()
            ->diffAlgorithm($options?->getDiffAlgorithm() ?? DiffAlgorithmType::MYERS)
            ->format($this->patternFactory->createPattern())
            ->ignoreCrAtEol()
            ->since($ruleConfig->startTime)
            ->until($ruleConfig->endTime);

        if ($options?->isExcludeMergeCommits() === true) {
            $this->builder->noMerges();
        }
        if ($options?->isIgnoreSpaceAtEol() === true) {
            $this->builder->ignoreSpaceAtEol();
        }
        if ($options?->isIgnoreSpaceChange() === true) {
            $this->builder->ignoreSpaceChange();
        }
        if ($options?->isIgnoreAllSpace() === true) {
            $this->builder->ignoreAllSpace();
        }
        if ($options?->isIgnoreBlankLines() === true) {
            $this->builder->ignoreBlankLines();
        }

        return $this->builder;
    }
}
