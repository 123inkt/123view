<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\CherryPick;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitCherryPickCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'cherry-pick');
    }

    public function strategy(string $strategy): self
    {
        $this->arguments['strategy'] = '--strategy=' . $strategy;

        return $this;
    }

    /**
     * Merge conflict resolution. ours or theirs.
     */
    public function conflictResolution(string $resolution): self
    {
        $this->arguments['conflict-resolution'] = '-X ' . $resolution;

        return $this;
    }

    public function noCommit(): self
    {
        $this->arguments['no-commit'] = '--no-commit';

        return $this;
    }

    /**
     * @param string[] $hashes
     */
    public function hashes(array $hashes): self
    {
        $this->arguments['hashes'] = implode(' ', $hashes);

        return $this;
    }

    public function continue(): self
    {
        $this->arguments['continue'] = '--continue';

        return $this;
    }

    public function abort(): self
    {
        $this->arguments['abort'] = '--abort';

        return $this;
    }
}
