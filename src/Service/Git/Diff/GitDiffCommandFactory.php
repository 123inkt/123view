<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitDiffCommandFactory
{
    public function __construct(private readonly GitDiffCommandBuilderFactory $builderFactory)
    {
    }

    public function diffHashes(Rule $rule, string $fromHash, string $toHash): GitCommandBuilderInterface
    {
        $options = $rule->getRuleOptions();

        $builder = $this->builderFactory->create();
        $builder->hashes($fromHash, $toHash)
            ->diffAlgorithm($options?->getDiffAlgorithm() ?? DiffAlgorithmType::MYERS)
            ->ignoreCrAtEol();

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
