<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Revision;

class RevisionPatternMatcher
{
    public function __construct(private string $matchingPattern)
    {
    }

    public function match(string $message): ?string
    {
        // match pattern against message
        if (preg_match('/' . $this->matchingPattern . '/', $message, $matches) === 1) {
            return $matches[0];
        }

        return null;
    }
}
