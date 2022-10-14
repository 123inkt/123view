<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Branch;

class GitBranchCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitBranchCommandBuilder
    {
        return new GitBranchCommandBuilder($this->git);
    }
}
