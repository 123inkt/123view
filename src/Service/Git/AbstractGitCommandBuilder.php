<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

abstract class AbstractGitCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    protected array $arguments = [];

    public function __construct(private readonly string $git, private readonly string $command)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = $this->command;
    }

    public function command(): string
    {
        return $this->command;
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
