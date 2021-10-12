<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\Rule;
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
            ->diffAlgorithm($rule->diffAlgorithm)
            ->format($this->patternFactory->createPattern())
            ->ignoreCrAtEol()
            ->since(Frequency::toSince($rule->frequency));

        if ($rule->excludeMergeCommits) {
            $this->builder->noMerges();
        }
        if ($rule->ignoreSpaceAtEol) {
            $this->builder->ignoreSpaceAtEol();
        }
        if ($rule->ignoreSpaceChange) {
            $this->builder->ignoreSpaceChange();
        }
        if ($rule->ignoreAllSpace) {
            $this->builder->ignoreAllSpace();
        }
        if ($rule->ignoreBlankLines) {
            $this->builder->ignoreBlankLines();
        }

        return $this->builder;
    }
}
