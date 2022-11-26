<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Reset;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitResetCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'reset';
    }

    public function hard(): self
    {
        $this->arguments['hard'] = '--hard';

        return $this;
    }

    public function command(): string
    {
        return 'reset';
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
