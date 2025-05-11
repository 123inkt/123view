<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep\Command;

use DR\Utils\Arrays;

class RipGrepCommandBuilder
{
    private string $command;

    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct()
    {
        $this->command = '/usr/bin/rg';
    }

    public function hidden(): self
    {
        $this->arguments['hidden'] = '--hidden';

        return $this;
    }

    public function noColor(): self
    {
        $this->arguments['color'] = '--color=never';

        return $this;
    }

    public function lineNumber(): self
    {
        $this->arguments['line-number'] = '--line-number';

        return $this;
    }

    public function beforeContext(int $lines): self
    {
        $this->arguments['before-context'] = '--before-context=' . $lines;

        return $this;
    }

    public function afterContext(int $lines): self
    {
        $this->arguments['after-context'] = '--after-context=' . $lines;

        return $this;
    }

    public function glob(string $pattern): self
    {
        $this->arguments['glob'][] = '--glob=' . $pattern;

        return $this;
    }

    public function json(): self
    {
        $this->arguments['json'] = '--json';

        return $this;
    }

    public function search(string $searchQuery): self
    {
        $this->arguments['search'] = $searchQuery;

        return $this;
    }

    public function build(): string
    {
        return $this->command . ' ' . array_map('escapeshellarg', Arrays::flatten($this->arguments));
    }

    public function __toString(): string
    {
        return $this->command . ' ' . implode(" ", $this->arguments);
    }
}
