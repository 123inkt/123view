<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Git\Log;

use DateTimeInterface;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class GitLogCommandBuilder implements GitCommandBuilderInterface
{
    /** @var array<string, string> */
    private array $arguments = [];

    public function __construct(string $git)
    {
        $this->arguments['app']     = $git;
        $this->arguments['command'] = 'log';
    }

    public function hashRange(string $fromHash, string $toHash): self
    {
        $this->arguments['hash-range'] = sprintf('%s..%s', $fromHash, $toHash);

        return $this;
    }

    public function remotes(): self
    {
        $this->arguments['remotes'] = '--remotes';

        return $this;
    }

    public function topoOrder(): self
    {
        $this->arguments['order'] = '--topo-order';

        return $this;
    }

    public function patch(): self
    {
        $this->arguments['patch'] = '--patch';

        return $this;
    }

    public function reverse(): self
    {
        $this->arguments['reverse'] = '--reverse';

        return $this;
    }

    public function noMerges(): self
    {
        $this->arguments['no-merges'] = '--no-merges';

        return $this;
    }

    public function maxCount(int $max): self
    {
        $this->arguments['max-count'] = '--max-count=' . $max;

        return $this;
    }

    public function since(DateTimeInterface $since): self
    {
        $this->arguments['since'] = sprintf('--since="%s"', $since->format('c'));

        return $this;
    }

    public function until(DateTimeInterface $until): self
    {
        $this->arguments['until'] = sprintf('--until="%s"', $until->format('c'));

        return $this;
    }

    public function decorate(string $decorate = 'full'): self
    {
        $this->arguments['decorate'] = sprintf('--decorate="%s"', $decorate);

        return $this;
    }

    public function format(string $format): self
    {
        $this->arguments['format'] = sprintf('--format="%s"', $format);

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

    public function ignoreBlankLines(): self
    {
        $this->arguments['ignore-blank-lines'] = '--ignore-blank-lines';

        return $this;
    }

    public function diffAlgorithm(string $algorithm = 'histogram'): self
    {
        $this->arguments['diff-algorithm'] = sprintf('--diff-algorithm="%s"', $algorithm);

        return $this;
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
