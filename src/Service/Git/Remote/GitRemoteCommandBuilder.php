<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Remote;

use DR\Review\Service\Git\AbstractGitCommandBuilder;
use League\Uri\Uri;

class GitRemoteCommandBuilder extends AbstractGitCommandBuilder
{
    public function __construct(string $git)
    {
        parent::__construct($git, 'remote');
    }

    public function setUrl(string $name, string $remoteUrl): self
    {
        $this->arguments['set-url']            = 'set-url';
        $this->arguments['set-url-name']       = $name;
        $this->arguments['set-url-remote-url'] = $remoteUrl;

        return $this;
    }

    public function __toString(): string
    {
        // strip sensitive data
        $arguments = $this->arguments;
        if (isset($arguments['set-url-remote-url'])) {
            $arguments['set-url-remote-url'] = (string)Uri::new($arguments['set-url-remote-url'])->withUserInfo(null);
        }

        return implode(" ", $arguments);
    }
}
