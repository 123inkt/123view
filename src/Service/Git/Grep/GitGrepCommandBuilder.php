<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Grep;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitGrepCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    private ?string $hash    = null;
    private ?string $pattern = null;

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'grep';
    }

    public function lineNumber(): self
    {
        $this->arguments['line-number'] = '-n';

        return $this;
    }

    public function noColor(): self
    {
        $this->arguments['no-color'] = '--no-color';

        return $this;
    }

    public function fullName(): self
    {
        $this->arguments['full-name'] = '--full-name';

        return $this;
    }

    public function context(int $context): self
    {
        $this->arguments['context'] = '--context ' . $context;

        return $this;
    }

    public function hash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Must be properly regex escaped by the caller
     */
    public function pattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function command(): string
    {
        return 'grep';
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        $values = array_values($this->arguments);
        if ($this->pattern !== null) {
            $values[] = escapeshellarg($this->pattern);
        }
        if ($this->hash !== null) {
            $values[] = $this->hash;
        }

        return $values;
    }

    public function __toString(): string
    {
        return implode(" ", $this->build());
    }
}
