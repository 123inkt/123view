<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\GarbageCollect;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitGarbageCollectCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'gc';
    }

    public function aggressive(): self
    {
        $this->arguments['aggressive'] = '--aggressive';

        return $this;
    }

    public function prune(string $date): self
    {
        $this->arguments['prune'] = sprintf('--prune=%s', $date);

        return $this;
    }

    public function quiet(): self
    {
        $this->arguments['quiet'] = '--quiet';

        return $this;
    }

    public function command(): string
    {
        return 'gc';
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
