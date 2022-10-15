<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;

class CommitsViewModel
{
    /** @var Commit[] */
    private array  $commits;
    private string $theme;
    /** @var ExternalLink[] */
    private array $externalLinks;

    /**
     * @param Commit[]       $commits
     * @param ExternalLink[] $externalLinks
     */
    public function __construct(array $commits, string $theme, array $externalLinks)
    {
        $this->commits       = $commits;
        $this->theme         = $theme;
        $this->externalLinks = $externalLinks;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @return ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
    }

    /**
     * @return Commit[]
     */
    public function getCommits(): array
    {
        return $this->commits;
    }

    /**
     * For the given block of changes, determine the maximum string length of the line numbers.
     *
     * @param bool $before if true, take the `before` line numbers, `after` otherwise.
     */
    public function getMaxLineNumberLength(DiffFile $file, bool $before): int
    {
        $length = 0;

        foreach ($file->blocks as $block) {
            foreach ($block->lines as $line) {
                $lineNumber = (string)($before ? $line->lineNumberBefore : $line->lineNumberAfter);
                $length     = max($length, strlen($lineNumber));
            }
        }

        return $length;
    }
}
