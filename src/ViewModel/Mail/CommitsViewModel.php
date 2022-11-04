<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ViewModel\Mail;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;

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

        foreach ($file->getBlocks() as $block) {
            foreach ($block->lines as $line) {
                $lineNumber = (string)($before ? $line->lineNumberBefore : $line->lineNumberAfter);
                $length     = max($length, strlen($lineNumber));
            }
        }

        return $length;
    }
}
