<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Log;

use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitCommandBuilderInterface;
use DR\Review\Utility\Assert;

class GitLogCommandFactory
{
    public function __construct(private GitCommandBuilderFactory $builderFactory, private FormatPatternFactory $patternFactory)
    {
    }

    public function fromRule(RuleConfiguration $ruleConfig): GitCommandBuilderInterface
    {
        $rule    = $ruleConfig->rule;
        $options = $rule->getRuleOptions();
        $builder = $this->builderFactory->createLog();
        $builder
            ->remotes()
            ->topoOrder()
            ->patch()
            ->decorate()
            ->diffAlgorithm($options?->getDiffAlgorithm() ?? DiffAlgorithmType::MYERS)
            ->format($this->patternFactory->createPattern())
            ->ignoreCrAtEol()
            ->since($ruleConfig->period->getStartDate())
            ->until(Assert::notNull($ruleConfig->period->getEndDate()));

        if ($options?->isExcludeMergeCommits() === true) {
            $builder->noMerges();
        }
        if ($options?->isIgnoreSpaceAtEol() === true) {
            $builder->ignoreSpaceAtEol();
        }
        if ($options?->isIgnoreSpaceChange() === true) {
            $builder->ignoreSpaceChange();
        }
        if ($options?->isIgnoreAllSpace() === true) {
            $builder->ignoreAllSpace();
        }
        if ($options?->isIgnoreBlankLines() === true) {
            $builder->ignoreBlankLines();
        }

        return $builder;
    }
}
