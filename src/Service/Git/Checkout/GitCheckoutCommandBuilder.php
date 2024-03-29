<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Checkout;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitCheckoutCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'checkout';
    }

    public function startPoint(string $commitHash): self
    {
        $this->arguments['start-point'] = $commitHash;

        return $this;
    }

    public function branch(string $branchName): self
    {
        $this->arguments['branch'] = '-b ' . $branchName;

        return $this;
    }

    public function command(): string
    {
        return 'checkout';
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
