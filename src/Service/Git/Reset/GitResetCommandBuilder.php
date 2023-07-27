<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Reset;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitResetCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'reset');
    }

    public function hard(): self
    {
        $this->arguments['hard'] = '--hard';

        return $this;
    }

    public function soft(): self
    {
        $this->arguments['soft'] = '--soft';

        return $this;
    }

    public function commitHash(string $commitHash): self
    {
        $this->arguments['hash'] = $commitHash;

        return $this;
    }
}
