<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\CherryPick;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitCherryPickCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'cherry-pick';
    }

    public function strategy(string $strategy): self
    {
        $this->arguments['strategy'] = 'strategy=' . $strategy;

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

    public function hashes(array $hashes): self
    {
        $this->arguments['hashes'] = implode(' ', $hashes);

        return $this;
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        return array_values($this->arguments);
    }

    public function __toString(): string
    {
        return implode(" ", $this->arguments);
    }
}
