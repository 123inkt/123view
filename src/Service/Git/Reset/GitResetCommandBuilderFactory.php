<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Reset;

class GitResetCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitResetCommandBuilder
    {
        return new GitResetCommandBuilder($this->git);
    }
}
