<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\CherryPick;

class GitCherryPickCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitCherryPickCommandBuilder
    {
        return new GitCherryPickCommandBuilder($this->git);
    }
}
