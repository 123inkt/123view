<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Diff;

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Service\Git\Checkout\GitCheckoutCommandBuilder;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitDiffCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitDiffCommandBuilder
    {
        return new GitDiffCommandBuilder($this->git);
    }
}
