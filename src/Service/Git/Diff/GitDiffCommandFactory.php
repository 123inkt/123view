<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitDiffCommandFactory
{
    private GitDiffCommandBuilder $builder;

    public function __construct(GitDiffCommandBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function diffHashes(Rule $rule, string $fromHash, string $toHash): GitCommandBuilderInterface
    {
        $this->builder
            ->start()
            ->hashes($fromHash, $toHash)
            ->diffAlgorithm($rule->diffAlgorithm)
            ->ignoreCrAtEol();

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
