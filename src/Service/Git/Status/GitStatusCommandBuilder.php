<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Status;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitStatusCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'status');
    }

    public function porcelain(): self
    {
        $this->arguments['porcelain'] = '--porcelain';

        return $this;
    }
}
