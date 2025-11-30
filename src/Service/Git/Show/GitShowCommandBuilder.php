<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitShowCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(private readonly string $git)
    {
        $this->arguments['app']     = $this->git;
        $this->arguments['command'] = 'show';
    }

    public function startPoint(string $hash): self
    {
        $this->arguments['start-point'] = $hash;

        return $this;
    }

    public function noPatch(): self
    {
        $this->arguments['no-patch'] = '--no-patch';

        return $this;
    }

    public function format(string $format): self
    {
        $this->arguments['format'] = sprintf('--format="%s"', $format);

        return $this;
    }

    public function unified(int $numberOfLines): self
    {
        $this->arguments['unified'] = '--unified=' . $numberOfLines;

        return $this;
    }

    public function ignoreCrAtEol(): self
    {
        $this->arguments['ignore-cr-at-eol'] = '--ignore-cr-at-eol';

        return $this;
    }

    public function ignoreSpaceAtEol(): self
    {
        $this->arguments['ignore-space-at-eol'] = '--ignore-space-at-eol';

        return $this;
    }

    public function ignoreSpaceChange(): self
    {
        $this->arguments['ignore-space-change'] = '--ignore-space-change';

        return $this;
    }

    public function ignoreAllSpace(): self
    {
        $this->arguments['ignore-all-space'] = '--ignore-all-space';

        return $this;
    }

    public function file(string $hash, string $filePath): self
    {
        $this->arguments['file'] = escapeshellarg(sprintf('%s:%s', $hash, $filePath));

        return $this;
    }

    public function base64encode(): self
    {
        $this->arguments['base64'] = '| base64';

        return $this;
    }

    public function command(): string
    {
        return 'show';
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
