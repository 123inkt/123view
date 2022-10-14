<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Show;

use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

class GitShowCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'show';
    }

    public function startPoint(string $hash): self
    {
        $this->arguments['start-point'] = $hash;

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
