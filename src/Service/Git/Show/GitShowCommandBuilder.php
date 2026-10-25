<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Service\Git\GitCommandBuilderInterface;

class GitShowCommandBuilder implements GitCommandBuilderInterface
{
    private readonly string $git;

    private ?string $startPoint      = null;
    private ?int    $unified         = null;
    private bool    $noPatch         = false;
    private ?string $format          = null;
    private ?string $fileRef         = null;
    private bool    $ignoreSpaceAtEol  = false;
    private bool    $ignoreCrAtEol     = false;
    private bool    $ignoreSpaceChange = false;
    private bool    $ignoreAllSpace    = false;
    private bool    $useBase64         = false;

    public function __construct(string $git)
    {
        $this->git = $git;
    }

    public function startPoint(string $hash): self
    {
        $this->startPoint = $hash;

        return $this;
    }

    public function noPatch(): self
    {
        $this->noPatch = true;

        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function unified(int $numberOfLines): self
    {
        $this->unified = $numberOfLines;

        return $this;
    }

    public function ignoreCrAtEol(): self
    {
        $this->ignoreCrAtEol = true;

        return $this;
    }

    public function ignoreSpaceAtEol(): self
    {
        $this->ignoreSpaceAtEol = true;

        return $this;
    }

    public function ignoreSpaceChange(): self
    {
        $this->ignoreSpaceChange = true;

        return $this;
    }

    public function ignoreAllSpace(): self
    {
        $this->ignoreAllSpace = true;

        return $this;
    }

    public function file(string $hash, string $filePath): self
    {
        $this->fileRef = sprintf('%s:%s', $hash, $filePath);

        return $this;
    }

    /**
     * Request base64-encoded output via a shell pipe.
     * Enabling this causes requiresShell() to return true.
     */
    public function base64encode(): self
    {
        $this->useBase64 = true;

        return $this;
    }

    public function command(): string
    {
        return 'show';
    }

    /**
     * Returns true when base64encode() was called: the resulting build() array contains
     * a shell pipe token and must be executed via fromShellCommandline().
     */
    public function requiresShell(): bool
    {
        return $this->useBase64;
    }

    /**
     * @return string[]
     */
    public function build(): array
    {
        $values = [$this->git, 'show'];

        if ($this->startPoint !== null) {
            $values[] = $this->startPoint;
        }

        if ($this->unified !== null) {
            $values[] = '--unified=' . $this->unified;
        }

        if ($this->noPatch) {
            $values[] = '--no-patch';
        }

        if ($this->format !== null) {
            // In shell mode the outer quotes are needed so the shell passes the value intact.
            $values[] = $this->useBase64
                ? sprintf('--format="%s"', $this->format)
                : '--format=' . $this->format;
        }

        if ($this->fileRef !== null) {
            // In shell mode escapeshellarg is required; in argv mode the value is passed verbatim.
            $values[] = $this->useBase64
                ? escapeshellarg($this->fileRef)
                : $this->fileRef;
        }

        array_push($values, ...$this->buildWhitespaceOptions());

        if ($this->useBase64) {
            $values[] = '| base64';
        }

        return $values;
    }

    public function __toString(): string
    {
        return implode(" ", $this->build());
    }

    /**
     * @return string[]
     */
    private function buildWhitespaceOptions(): array
    {
        $options = [];
        if ($this->ignoreSpaceAtEol) {
            $options[] = '--ignore-space-at-eol';
        }
        if ($this->ignoreCrAtEol) {
            $options[] = '--ignore-cr-at-eol';
        }
        if ($this->ignoreSpaceChange) {
            $options[] = '--ignore-space-change';
        }
        if ($this->ignoreAllSpace) {
            $options[] = '--ignore-all-space';
        }

        return $options;
    }
}
