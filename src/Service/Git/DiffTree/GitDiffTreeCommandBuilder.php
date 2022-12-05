<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\DiffTree;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitDiffTreeCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'diff-tree';
    }

    public function noCommitId(): self
    {
        $this->arguments['no-commit-id'] = '--no-commit-id';

        return $this;
    }

    public function nameOnly(): self
    {
        $this->arguments['name-only'] = '--name-only';

        return $this;
    }

    public function recurseSubTree(): self
    {
        $this->arguments['recurse-sub-tree'] = '-r';

        return $this;
    }

    public function hash(string $hash): self
    {
        $this->arguments['hash'] = $hash;

        return $this;
    }

    public function command(): string
    {
        return 'diff-tree';
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
