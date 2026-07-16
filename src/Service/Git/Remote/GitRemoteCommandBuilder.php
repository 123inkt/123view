<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Service\Git\AbstractGitCommandBuilder;
use DR\Review\Service\Git\SensitiveGitCommandBuilderInterface;

class GitRemoteCommandBuilder extends AbstractGitCommandBuilder implements SensitiveGitCommandBuilderInterface
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'remote');
    }

    public function setUrl(string $name, string $remoteUrl): self
    {
        $this->arguments['set-url']       = 'set-url';
        $this->arguments['set-url-name']  = $name;
        $this->arguments['set-url-value'] = $remoteUrl;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getSensitiveReplacements(): array
    {
        if (!isset($this->arguments['set-url-value'])) {
            return [];
        }

        $url = $this->arguments['set-url-value'];
        $redacted = preg_replace('#://[^@]+@#', '://***@', $url) ?? $url;

        return $redacted !== $url ? [$url => $redacted] : [];
    }

    public function __toString(): string
    {
        // strip sensitive data from the logged string
        $arguments = $this->arguments;
        if (isset($arguments['set-url-value'])) {
            $arguments['set-url-value'] = '*************';
        }

        return implode(" ", $arguments);
    }
}
