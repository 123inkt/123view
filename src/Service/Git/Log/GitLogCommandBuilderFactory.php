<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DateTimeInterface;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitLogCommandBuilderFactory
{
    public function __construct(private string $git)
    {
    }

    public function create(): GitLogCommandBuilder
    {
        return new GitLogCommandBuilder($this->git);
    }
}
