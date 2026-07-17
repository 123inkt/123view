<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Add;

use DR\Review\Service\Git\GitCommandBuilderInterface;
use LogicException;

class GitAddCommandBuilder implements GitCommandBuilderInterface
{
    /** @var string[] */
    private array $paths = [];

    public function __construct(private readonly string $git)
    {
    }

    public function paths(string ...$paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    public function command(): string
    {
        return 'add';
    }

    public function requiresShell(): bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        if (count($this->paths) === 0) {
            throw new LogicException('At least one path is required for git add.');
        }

        return [$this->git, 'add', '--', ...$this->paths];
    }

    public function __toString(): string
    {
        return implode(' ', $this->build());
    }
}
