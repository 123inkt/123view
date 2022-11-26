<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Branch;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitBranchCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'branch';
    }

    public function delete(string $ref): self
    {
        $this->arguments['strategy'] = '-D ' . $ref;

        return $this;
    }

    public function command(): string
    {
        return 'branch';
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
