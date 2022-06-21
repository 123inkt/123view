<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Entity\Rule;
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

    public function fromRule(Rule $rule): GitCommandBuilderInterface
    {
        $this->builder
            ->start()
            ->remotes()
            ->topoOrder()
            ->patch()
            ->decorate()
            ->diffAlgorithm($rule->getRuleOptions()?->getDiffAlgorithm() ?? DiffAlgorithmType::MYERS)
            ->format($this->patternFactory->createPattern())
            ->ignoreCrAtEol()
            ->since($rule->config->startTime)
            ->until($rule->config->endTime);

        if ($rule->getRuleOptions()?->isExcludeMergeCommits() === true) {
            $this->builder->noMerges();
        }
        if ($rule->getRuleOptions()?->isIgnoreSpaceAtEol() === true) {
            $this->builder->ignoreSpaceAtEol();
        }
        if ($rule->getRuleOptions()?->isIgnoreSpaceChange() === true) {
            $this->builder->ignoreSpaceChange();
        }
        if ($rule->getRuleOptions()?->isIgnoreAllSpace() === true) {
            $this->builder->ignoreAllSpace();
        }
        if ($rule->getRuleOptions()?->isIgnoreBlankLines() === true) {
            $this->builder->ignoreBlankLines();
        }

        return $this->builder;
    }
}
