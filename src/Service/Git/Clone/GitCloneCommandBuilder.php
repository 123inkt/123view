<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Clone;

use DR\Review\Service\Git\SensitiveGitCommandBuilderInterface;
use LogicException;

class GitCloneCommandBuilder implements SensitiveGitCommandBuilderInterface
{
    private readonly string $git;

    private ?string $repositoryUrl  = null;
    private ?string $directoryPath  = null;

    public function __construct(string $git)
    {
        $this->git = $git;
    }

    public function repository(string $url): self
    {
        $this->repositoryUrl = $url;

        return $this;
    }

    public function directory(string $path): self
    {
        $this->directoryPath = $path;

        return $this;
    }

    public function command(): string
    {
        return 'clone';
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
        if ($this->repositoryUrl === null) {
            throw new LogicException('Repository URL is required for git clone.');
        }
        if ($this->directoryPath === null) {
            throw new LogicException('Target directory is required for git clone.');
        }

        return [$this->git, 'clone', '-q', '--end-of-options', $this->repositoryUrl, $this->directoryPath];
    }

    /**
     * @return array<string, string>
     */
    public function getSensitiveReplacements(): array
    {
        if ($this->repositoryUrl === null || !str_contains($this->repositoryUrl, '@')) {
            return [];
        }

        $redacted = preg_replace('#://[^@]+@#', '://***@', $this->repositoryUrl) ?? $this->repositoryUrl;

        return $redacted !== $this->repositoryUrl ? [$this->repositoryUrl => $redacted] : [];
    }

    public function __toString(): string
    {
        $url = $this->repositoryUrl ?? '<url>';
        foreach ($this->getSensitiveReplacements() as $search => $replace) {
            $url = str_replace($search, $replace, $url);
        }

        return implode(' ', [$this->git, 'clone', '-q', '--end-of-options', $url, $this->directoryPath ?? '<directory>']);
    }
}
