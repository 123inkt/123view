<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\RevList;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitRevListCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'rev-list';
    }

    public function commitRange(string $start, string $end = null): self
    {
        $this->arguments['commits'] = $start . '...' . $end;

        return $this;
    }

    public function leftOnly(): self
    {
        $this->arguments['left-only'] = '--left-only';

        return $this;
    }

    public function rightOnly(): self
    {
        $this->arguments['right-only'] = '--right-only';

        return $this;
    }

    public function leftRight(): self
    {
        $this->arguments['left-right'] = '--left-right';

        return $this;
    }

    public function pretty(string $format): self
    {
        $this->arguments['pretty'] = '-pretty=' . $format;

        return $this;
    }

    public function noMerges(): self
    {
        $this->arguments['no-merges'] = '--no-merges';

        return $this;
    }

    public function command(): string
    {
        return 'rev-list';
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
