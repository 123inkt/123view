<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Clean;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitCleanCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'clean';
    }

    public function force(): self
    {
        $this->arguments['force'] = '--force';

        return $this;
    }

    public function recurseDirectories(): self
    {
        $this->arguments['recurse-directories'] = '-d';

        return $this;
    }

    public function skipIgnoreRules(): self
    {
        $this->arguments['recurse-directories'] = '-x';

        return $this;
    }

    public function command(): string
    {
        return 'clean';
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
