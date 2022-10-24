<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Revision;

class RevisionPatternMatcher
{
    /** @var string[] */
    private array $matchingGroups;

    public function __construct(private string $matchingPattern, string $matchingGroups)
    {
        $this->matchingGroups = $matchingGroups === '' ? [] : explode(',', $matchingGroups);
    }

    public function match(string $message): ?string
    {
        // match pattern against message
        if (preg_match('/' . $this->matchingPattern . '/', $message, $matches) === 1) {
            foreach ($this->matchingGroups as $matchingGroup) {
                if (isset($matches[$matchingGroup]) && $matches[$matchingGroup] !== '') {
                    return $matches[$matchingGroup];
                }
            }

            return $matches[0];
        }

        return null;
    }
}
