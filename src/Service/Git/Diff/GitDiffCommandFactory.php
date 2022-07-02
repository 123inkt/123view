<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
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
        $options = $rule->getRuleOptions();

        $this->builder
            ->start()
            ->hashes($fromHash, $toHash)
            ->diffAlgorithm($options?->getDiffAlgorithm() ?? DiffAlgorithmType::MYERS)
            ->ignoreCrAtEol();

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
