<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitDiffCommandFactory
{
    public function __construct(private readonly GitCommandBuilderFactory $builderFactory)
    {
    }

    public function diffHashes(Rule $rule, string $fromHash, string $toHash): GitCommandBuilderInterface
    {
        $options = $rule->getRuleOptions();

        $builder = $this->builderFactory->createDiff();
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
