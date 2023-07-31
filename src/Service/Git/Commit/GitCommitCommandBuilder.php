<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitCommitCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'commit');
    }

    public function allowEmpty(): self
    {
        $this->arguments['allow-empty'] = '--allow-empty';

        return $this;
    }

    public function message(string $message): self
    {
        $this->arguments['message'] = '-m ' . escapeshellarg($message);

        return $this;
    }
}
