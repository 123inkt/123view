<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Show;

class GitShowCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitShowCommandBuilder
    {
        return new GitShowCommandBuilder($this->git);
    }
}
