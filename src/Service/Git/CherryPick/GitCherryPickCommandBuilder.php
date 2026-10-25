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
        $this->arguments['conflict-resolution-flag']  = '-X';
        $this->arguments['conflict-resolution-value'] = $resolution;

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
        // remove any previously set hash entries
        foreach (array_keys($this->arguments) as $key) {
            if (str_starts_with($key, 'hash-')) {
                unset($this->arguments[$key]);
            }
        }

        foreach ($hashes as $index => $hash) {
            $this->arguments['hash-' . $index] = $hash;
        }

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
