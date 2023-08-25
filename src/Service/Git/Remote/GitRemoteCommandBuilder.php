<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Service\Git\AbstractGitCommandBuilder;

class GitRemoteCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'remote');
    }

    public function setUrl(string $name, string $remoteUrl): self
    {
        $this->arguments['set-url'] = sprintf('set-url %s %s', escapeshellarg($name), escapeshellarg($remoteUrl));

        return $this;
    }

    public function __toString(): string
    {
        // strip sensitive data
        $arguments = $this->arguments;
        if (isset($arguments['set-url'])) {
            $arguments['set-url'] = 'set-url *************';
        }

        return implode(" ", $arguments);
    }
}
