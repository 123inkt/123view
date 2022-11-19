<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\Mail;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Utility\Arrays;

class CommitsViewModel
{
    /** @var Commit[] */
    private array  $commits;
    private string $theme;

    /**
     * @param Commit[] $commits
     */
    public function __construct(array $commits, string $theme)
    {
        $this->commits = $commits;
        $this->theme   = $theme;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @return string[]
     */
    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->commits as $commit) {
            $authors[] = $commit->author->name;
        }

        return Arrays::unique($authors);
    }

    /**
     * @return Commit[]
     */
    public function getCommits(): array
    {
        return $this->commits;
    }
}
