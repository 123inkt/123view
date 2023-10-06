<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Fetch;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitFetchCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(string $git)
    {
        $this->arguments['app']     = $git;
        $this->arguments['command'] = 'fetch';
    }

    public function verbose(): self
    {
        $this->arguments['verbose'] = '--verbose';

        return $this;
    }

    public function all(): self
    {
        $this->arguments['all'] = '--all';

        return $this;
    }

    public function noTags(): self
    {
        $this->arguments['no-tags'] = '--no-tags';

        return $this;
    }

    public function prune(): self
    {
        $this->arguments['prune'] = '--prune';

        return $this;
    }

    public function command(): string
    {
        return 'fetch';
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
