<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Grep;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitGrepCommandBuilder extends AbstractGitCommandBuilder
{
    private ?string $hash    = null;
    private ?string $pattern = null;

    public function __construct(string $git)
    {
        parent::__construct($git, 'grep');
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
        $this->arguments['context-flag']  = '--context';
        $this->arguments['context-value'] = (string)$context;

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

    /**
     * @return string[]
     */
    public function build(): array
    {
        $values = parent::build();
        if ($this->pattern !== null) {
            $values[] = $this->pattern;
        }
        if ($this->hash !== null) {
            $values[] = $this->hash;
        }

        return $values;
    }

    public function __toString(): string
    {
        return implode(' ', $this->build());
    }
}
