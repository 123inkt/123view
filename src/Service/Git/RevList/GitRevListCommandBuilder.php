<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitRevListCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'rev-list');
    }

    public function commitRange(string $start, string $end): self
    {
        $this->arguments['commits'] = $start . '...' . $end;

        return $this;
    }

    public function leftOnly(): self
    {
        $this->arguments['left-only'] = '--left-only';

        return $this;
    }

    public function rightOnly(): self
    {
        $this->arguments['right-only'] = '--right-only';

        return $this;
    }

    public function leftRight(): self
    {
        $this->arguments['left-right'] = '--left-right';

        return $this;
    }

    public function pretty(string $format): self
    {
        $this->arguments['pretty'] = '--pretty=' . $format;

        return $this;
    }

    public function noMerges(): self
    {
        $this->arguments['no-merges'] = '--no-merges';

        return $this;
    }
}
