<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Service\Git\AbstractGitCommandBuilder;
use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitCommitCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'commit');
    }

    public function message(string $message): GitCommandBuilderInterface
    {
        $this->arguments['message'] = '-m ' . escapeshellarg($message);

        return $this;
    }
}
