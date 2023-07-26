<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Commit;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitCommitCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'commit';
    }

    public function message(string $message): self
    {
        $this->arguments['message'] = '-m ' . escapeshellarg($message);

        return $this;
    }

    public function command(): string
    {
        return 'commit';
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
