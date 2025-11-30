<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\LsTree;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitLsTreeCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    /** @var list<string> */
    private array $files = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'ls-tree';
    }

    public function hash(string $hash): self
    {
        $this->arguments['hash'] = $hash;

        return $this;
    }

    public function recursive(): self
    {
        $this->arguments['recursive'] = '-r';

        return $this;
    }

    public function nameOnly(): self
    {
        $this->arguments['name-only'] = '--name-only';

        return $this;
    }

    public function file(string ...$filepaths): self
    {
        $this->files = array_values(array_filter($filepaths, static fn(string $filepath) => trim($filepath) !== ''));

        return $this;
    }

    public function command(): string
    {
        return 'ls-tree';
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        $values = array_values($this->arguments);

        if (count($this->files) > 0) {
            $values[] = '--';
            $values   = array_merge($values, array_map('escapeshellarg', $this->files));
        }

        return $values;
    }

    public function __toString(): string
    {
        return implode(" ", $this->build());
    }
}
