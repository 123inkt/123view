<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Add;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitAddCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'add';
    }

    public function setPath(string $path): self
    {
        $this->arguments['path'] = $path;

        return $this;
    }

    public function command(): string
    {
        return 'add';
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
